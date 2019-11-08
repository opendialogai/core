<?php

namespace OpenDialogAi\ConversationBuilder;

use Closure;
use Ds\Set;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ConversationBuilder\Exceptions\ConditionDoesNotDefineOperationException;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationModel;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationScenes;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationYaml;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationYamlSchema;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\ExpectedAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Interpreter;
use OpenDialogAi\Core\Conversation\InvalidConversationStatusTransitionException;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutationResponse;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Spatie\Activitylog\Traits\LogsActivity;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @property string status
 * @property string yaml_validation_status
 * @property string yaml_schema_validation_status
 * @property string scenes_validation_status
 * @property string model_validation_status
 * @property mixed model
 * @property int id
 * @property string name
 * @property int version_number
 * @property array opening_intents
 * @property array outgoing_intents
 * @property array history
 * @property bool has_been_used
 */
class Conversation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'model',
        'notes'
    ];

    protected $visible = [
        'id',
        'name',
        'status',
        'yaml_validation_status',
        'yaml_schema_validation_status',
        'scenes_validation_status',
        'model_validation_status',
        'model',
        'notes',
        'created_at',
        'updated_at',
        'version_number',
        'history',
        'opening_intents',
        'outgoing_intents',
        'has_been_used'
    ];

    protected $appends = [
        'history',
        'opening_intents',
        'outgoing_intents',
        'has_been_used'
    ];

    // Create activity logs when the model or notes attribute is updated.
    protected static $logAttributes = ['model', 'notes', 'status', 'version_number', 'graph_uid'];

    protected static $logName = 'conversation_log';

    protected static $submitEmptyLogs = false;

    // Don't create activity logs when these model attributes are updated.
    protected static $ignoreChangedAttributes = [
        'updated_at',
        'yaml_validation_status',
        'yaml_schema_validation_status',
        'scenes_validation_status',
        'model_validation_status'
    ];

    /**
     * Get the logs for the conversation.
     */
    public function conversationStateLogs()
    {
        return $this->hasMany('OpenDialogAi\ConversationBuilder\ConversationStateLog');
    }

    /**
     * Override save to add our validation jobs.
     * @param array $options
     */
    public function save(array $options = [])
    {
        // Determine if we're in the process of validation.
        $doValidation = (!isset($options['validate']) || $options['validate'] === true);
        // Reset validation status.
        if ($doValidation) {
            $this->status = ConversationNode::SAVED;
            $this->yaml_validation_status = 'waiting';
            $this->yaml_schema_validation_status = 'waiting';
            $this->scenes_validation_status = 'waiting';
            $this->model_validation_status = 'waiting';
        }

        parent::save($options);

        // Create validation jobs.
        if ($doValidation) {
            ValidateConversationYaml::dispatch($this)->chain([
                new ValidateConversationYamlSchema($this),
                new ValidateConversationScenes($this),
                new ValidateConversationModel($this)
            ]);

            $this->refresh();
        }
    }

    /**
     * Build the conversation's representation.
     *
     * @return ConversationNode
     * @throws BindingResolutionException
     */
    public function buildConversation()
    {
        try {
            $yaml = Yaml::parse($this->model)['conversation'];
        } catch (ParseException $exception) {
            Log::error('Could not parse conversation yaml!');
            throw $exception;
        }

        $conversationStore = app()->make(ConversationStoreInterface::class);
        $conversationManager = new ConversationManager($yaml['id'], $this->status, $this->version_number ?: 0);

        if ($conversationManager->getConversationVersion() > 0) {
            $previousTemplate = $conversationStore->getLatestTemplateVersionByName($yaml['id']);

            if ($previousTemplate) {
                $conversationManager->setUpdateOf($previousTemplate);
            }
        }

        if (isset($yaml['conditions'])) {
            $this->addConversationConditions($yaml['conditions'], $conversationManager);
        }

        // Build the conversation in two steps. First all the scenes and then all the intents as
        // intents may connect between scenes.
        foreach ($yaml['scenes'] as $sceneId => $scene) {
            $sceneIsOpeningScene = $sceneId === 'opening_scene';
            $conversationManager->createScene($sceneId, $sceneIsOpeningScene);

            if (!$sceneIsOpeningScene && isset($scene['conditions'])) {
                $this->addSceneConditions($sceneId, $scene['conditions'], $conversationManager);
            }
        }

        // Now cycle through the scenes again and identifying intents that cut across scenes.
        $intentIdx = 1;

        foreach ($yaml['scenes'] as $sceneId => $scene) {
            foreach ($scene['intents'] as $intent) {
                $speaker = null;
                $intentSceneId = null;
                $intentNode = $this->createIntent($intent, $speaker, $intentSceneId);

                if (isset($intentSceneId)) {
                    if ($speaker === 'u') {
                        // phpcs:ignore
                        $conversationManager->userSaysToBotAcrossScenes($sceneId, $intentSceneId, $intentNode, $intentIdx);
                    } elseif ($speaker === 'b') {
                        // phpcs:ignore
                        $conversationManager->botSaysToUserAcrossScenes($sceneId, $intentSceneId, $intentNode, $intentIdx);
                    } else {
                        Log::debug("I don't know about the speaker type '{$speaker}'");
                    }
                } elseif ($speaker === 'u') {
                    $conversationManager->userSaysToBot($sceneId, $intentNode, $intentIdx);
                } elseif ($speaker === 'b') {
                    $conversationManager->botSaysToUser($sceneId, $intentNode, $intentIdx);
                } else {
                    Log::debug("I don't know about the speaker type '{$speaker}'");
                }

                $intentIdx++;
            }
        }

        return $conversationManager->getConversation();
    }

    /**
     * Activate the conversation in DGraph.
     *
     * @param ConversationNode $conversation
     * @return bool
     * @throws BindingResolutionException
     */
    public function activateConversation(ConversationNode $conversation): bool
    {
        $cm = ConversationManager::createManagerForExistingConversation($conversation);

        try {
            $cm->setActivated();
        } catch (InvalidConversationStatusTransitionException $e) {
            Log::warning($e->getMessage());
            return false;
        }

        $dGraph = app()->make(DGraphClient::class);
        $mutation = new DGraphMutation($cm->getConversation());

        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $dGraph->tripleMutation($mutation);
        if ($mutationResponse->isSuccessful()) {
            $previousGraphUid = $this->graph_uid;

            // Set conversation status to "activated".
            $this->status = ConversationNode::ACTIVATED;
            $this->graph_uid = $mutationResponse->getData()['uids'][$this->name];
            $this->version_number++;

            $this->save(['validate' => false]);

            ConversationStateLog::create([
                'conversation_id' => $this->id,
                'message' => 'Activated conversation in DGraph.',
                'type' => 'activate_conversation',
            ])->save();

            if ($previousGraphUid) {
                return $this->deactivatePrevious($previousGraphUid, $dGraph);
            }

            return true;
        } else {
            foreach ($mutationResponse->getErrors() as $error) {
                Log::debug(
                    sprintf(
                        'DGraph error - %s: %s',
                        $error['extensions']['code'],
                        $error['message']
                    )
                );
            }
        }

        return false;
    }

    /**
     * @param $previousUid
     * @param DGraphClient $dGraph
     * @return bool
     * @throws BindingResolutionException
     */
    private function deactivatePrevious($previousUid, DGraphClient $dGraph): bool
    {
        /** @var ConversationStoreInterface $conversationStore */
        $conversationStore = app()->make(ConversationStoreInterface::class);

        $previousConversation = $conversationStore->getConversationTemplateByUid($previousUid);

        /** @var ConversationManager $cmPrevious */
        $cmPrevious = ConversationManager::createManagerForExistingConversation($previousConversation);

        try {
            $cmPrevious->setDeactivated();
        } catch (InvalidConversationStatusTransitionException $e) {
            Log::warning(
                sprintf(
                    "Cannot deactivate previous conversation when activating version %d.",
                    $this->version_number
                )
            );

            return false;
        }

        $mutation = new DGraphMutation($cmPrevious->getConversation());

        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $dGraph->tripleMutation($mutation);

        if (!$mutationResponse->isSuccessful()) {
            Log::warning(
                sprintf(
                    "Failed to deactivate previous conversation when activating version %d.",
                    $this->version_number
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Deactivate the conversation in DGraph.
     * @return bool
     * @throws BindingResolutionException
     */
    public function deactivateConversation(): bool
    {
        return $this->setStatus(function (ConversationManager $cm) {
            $cm->setDeactivated();
        }, ConversationNode::DEACTIVATED);
    }

    /**
     * Archiving the conversation
     * @return bool
     * @throws BindingResolutionException
     */
    public function archiveConversation(): bool
    {
        return $this->setStatus(function (ConversationManager $cm) {
            $cm->setArchived();
        }, ConversationNode::ARCHIVED);
    }

    /**
     * @param $intent
     * @param $speaker
     * @param $intentSceneId
     * @return Intent
     */
    private function createIntent($intent, &$speaker, &$intentSceneId): Intent
    {
        $speaker = array_keys($intent)[0];
        $intentValue = $intent[$speaker];

        $actionLabel = null;
        $interpreterLabel = null;
        $confidence = null;
        $completes = false;
        $expectedAttributes = null;
        $conditions = null;
        $inputActionAttributes = null;
        $outputActionAttributes = null;

        if (is_array($intentValue)) {
            $intentLabel = $intentValue['i'];
            $interpreterLabel = $intentValue['interpreter'] ?? null;
            $completes = $intentValue['completes'] ?? false;
            $confidence = $intentValue['confidence'] ?? false;
            $expectedAttributes = $intentValue['expected_attributes'] ?? null;
            $conditions = $intentValue['conditions'] ?? null;
            $intentSceneId = $intent[$speaker]['scene'] ?? null;
            $inputAttributes = $intent[$speaker]['input_attributes'] ?? null;
            $expectedAttributes = $intent[$speaker]['expected_attributes'] ?? null;

            if (isset($intentValue['action']) && is_array($intentValue['action'])) {
                $actionLabel = $intentValue['action']['id'] ?? null;
                $inputActionAttributes = $intentValue['action']['input_attributes'] ?? null;
                $outputActionAttributes = $intentValue['action']['output_attributes'] ?? null;
            } else {
                $actionLabel = $intentValue['action'] ?? null;
            }
        } else {
            $intentLabel = $intentValue;
        }

        /* @var Intent $intentNode */
        $intentNode = new Intent($intentLabel, $completes);

        if ($actionLabel) {
            $intentNode->addAction(new Action($actionLabel));
        }

        if ($interpreterLabel) {
            $intentNode->addInterpreter(new Interpreter($interpreterLabel));
        }

        if ($confidence) {
            $intentNode->setConfidence($confidence);
        }

        if (is_array($expectedAttributes)) {
            foreach ($expectedAttributes as $expectedAttribute) {
                $intentNode->addExpectedAttribute(new ExpectedAttribute($expectedAttribute['id']));
            }
        }

        if (is_array($conditions)) {
            $conditionObjects = $this->createConditions($conditions);

            foreach ($conditionObjects as $condition) {
                $intentNode->addCondition($condition);
            }
        }

        if (is_array($inputActionAttributes)) {
            foreach ($inputActionAttributes as $inputActionAttribute) {
                $intentNode->addInputActionAttribute(new ExpectedAttribute($inputActionAttribute));
            }
        }

        if (is_array($outputActionAttributes)) {
            foreach ($outputActionAttributes as $outputActionAttribute) {
                $intentNode->addOutputActionAttribute(new ExpectedAttribute($outputActionAttribute));
            }
        }

        return $intentNode;
    }

    /**
     * @param array $conditions
     * @param ConversationManager $cm
     */
    public function addConversationConditions(array $conditions, ConversationManager $cm)
    {
        $conditionObjects = $this->createConditions($conditions);

        foreach ($conditionObjects as $condition) {
            $cm->addConditionToConversation($condition);
        }
    }

    /**
     * @param $sceneId
     * @param $conditions
     * @param ConversationManager $conversationManager
     */
    public function addSceneConditions($sceneId, $conditions, ConversationManager $conversationManager)
    {
        $conditionObjects = $this->createConditions($conditions);
        $scene = $conversationManager->getScene($sceneId);

        foreach ($conditionObjects as $condition) {
            $scene->addCondition($condition);
        }
    }

    /**
     * @param array $condition
     * @return Condition
     */
    private function createCondition(array $condition)
    {
        $operation = isset($condition['operation']) ? $condition['operation'] : null;
        $attributes = isset($condition['attributes']) ? $condition['attributes'] : [];
        $parameters = isset($condition['parameters']) ? $condition['parameters'] : [];

        // Now check that we have a valid operation.
        if (!isset($operation)) {
            throw new ConditionDoesNotDefineOperationException(
                sprintf('Condition does not define an operation')
            );
        }

        // Now we can create the condition - we set an id as a helper
        $id = sprintf('%s-%s-%s', implode($attributes), $operation, implode($parameters));
        $condition = new Condition($operation, $attributes, $parameters, $id);
        Log::debug('Created condition from Yaml.');
        return $condition;
    }

    /**
     * @param Closure $managerMethod
     * @param $newStatus
     * @return bool
     * @throws BindingResolutionException
     */
    private function setStatus(Closure $managerMethod, $newStatus): bool
    {
        $dGraph = app()->make(DGraphClient::class);

        /** @var ConversationStoreInterface $conversationStore */
        $conversationStore = app()->make(ConversationStoreInterface::class);

        $conversation = $conversationStore->getConversationTemplateByUid($this->graph_uid);

        /** @var ConversationManager $cm */
        $cm = ConversationManager::createManagerForExistingConversation($conversation);

        try {
            $managerMethod->call($this, $cm);
        } catch (InvalidConversationStatusTransitionException $e) {
            return false;
        }

        $mutation = new DGraphMutation($cm->getConversation());

        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $dGraph->tripleMutation($mutation);

        if ($mutationResponse->isSuccessful()) {
            $this->status = $newStatus;
            $this->save(['validate' => false]);

            // Add log message.
            ConversationStateLog::create([
                'conversation_id' => $this->id,
                'message' => 'Deactivated conversation in DGraph.',
                'type' => 'deactivate_conversation',
            ])->save();

            return true;
        }

        return false;
    }

    /**
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeWithoutStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', '!=', $status);
    }

    /**
     * @return array
     */
    public function getOutgoingIntentsAttribute(): array
    {
        $outgoingIntents = [];
        $yaml = Yaml::parse($this->model)['conversation'];

        foreach ($yaml['scenes'] as $sceneId => $scene) {
            foreach ($scene['intents'] as $intent) {
                foreach ($intent as $tag => $value) {
                    if ($tag == 'b') {
                        foreach ($value as $key => $intent) {
                            if ($key == 'i') {
                                $outgoingIntent = OutgoingIntent::where('name', $intent)->first();
                                if ($outgoingIntent) {
                                    $outgoingIntents[] = [
                                        'id' => $outgoingIntent->id,
                                        'name' => $intent,
                                    ];
                                } else {
                                    $outgoingIntents[] = [
                                        'name' => $intent,
                                    ];
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $outgoingIntents;
    }

    /**
     * @return array
     */
    public function getOpeningIntentsAttribute(): array
    {
        $yaml = Yaml::parse($this->model)['conversation'];

        $intents = [];

        foreach ($yaml['scenes'] as $sceneId => $scene) {
            foreach ($scene['intents'] as $intent) {
                foreach ($intent as $tag => $value) {
                    if ($tag == 'b') {
                        return $intents;
                    }

                    if ($tag == 'u') {
                        foreach ($value as $key => $intent) {
                            if ($key == 'i') {
                                $intents[] = $intent;
                            }
                        }
                    }
                }
            }
        }

        return $intents;
    }

    /**
     * @return array
     */
    public function getHistoryAttribute(): array
    {

        $history = ConversationActivity::forSubjectOrdered($this->id)->get();

        return $history->filter(function ($item) {
            // Retain if it's the first activity record or if it's a record with the version has incremented
            return isset($item['properties']['old'])
                && $item['properties']['attributes']['version_number'] != $item['properties']['old']['version_number'];
        })->values()->map(function ($item) {
            return [
                'id' => $item['id'],
                'timestamp' => $item['updated_at'],
                'attributes' => $item['properties']['attributes']
            ];
        })->toArray();
    }

    /**
     * @return bool
     * @throws BindingResolutionException
     */
    public function getHasBeenUsedAttribute(): bool
    {
        /** @var ConversationStoreInterface $conversationStore */
        $conversationStore = app()->make(ConversationStoreInterface::class);

        return $conversationStore->hasConversationBeenUsed($this->name);
    }

    /**
     * @param array $conditions
     * @return Set
     */
    private function createConditions(array $conditions): Set
    {
        $conditionObjects = new Set();

        foreach ($conditions as $key => $condition) {
            try {
                $conditionObject = $this->createCondition($condition['condition']);
                $conditionObjects->add($conditionObject);
            } catch (Exception $e) {
                Log::debug(
                    sprintf(
                        'Could not create condition because: %s',
                        $e->getMessage()
                    )
                );
            }
        }
        return $conditionObjects;
    }
}
