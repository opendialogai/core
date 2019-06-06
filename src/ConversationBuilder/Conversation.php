<?php

namespace OpenDialogAi\ConversationBuilder;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ConversationBuilder\Exceptions\ConditionDoesNotDefineAttributeException;
use OpenDialogAi\ConversationBuilder\Exceptions\ConditionDoesNotDefineOperationException;
use OpenDialogAi\ConversationBuilder\Exceptions\ConditionDoesNotDefineValidOperationException;
use OpenDialogAi\ConversationBuilder\Exceptions\ConditionRequiresValueButDoesNotDefineItException;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationModel;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationScenes;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationYaml;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationYamlSchema;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\ConversationQueryFactory;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\ExpectedAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Interpreter;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutationResponse;
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
 */
class Conversation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'model',
        'notes',
    ];

    // Create activity logs when the model or notes attribute is updated.
    protected static $logAttributes = ['model', 'notes'];

    protected static $logName = 'conversation_log';

    protected static $logOnlyDirty = true;

    // Don't create activity logs when these model attributes are updated.
    protected static $ignoreChangedAttributes = [
        'updated_at',
        'status',
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
            $this->status = 'imported';
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
        }
    }

    /**
     * Build the conversation's representation.
     *
     * @return ConversationNode
     */
    public function buildConversation()
    {
        try {
            $yaml = Yaml::parse($this->model)['conversation'];
        } catch (ParseException $exception) {
            Log::error('Could not parse conversation yaml!');
            throw $exception;
        }

        $conversationManager = new ConversationManager($yaml['id']);

        if (isset($yaml['conditions'])) {
            $this->addConversationConditions($yaml['conditions'], $conversationManager);
        }

        // Build the conversation in two steps. First all the scenes and then all the intents as
        // intents may connect between scenes.
        foreach ($yaml['scenes'] as $sceneId => $scene) {
            $sceneIsOpeningScene = $sceneId === 'opening_scene';
            $conversationManager->createScene($sceneId, $sceneIsOpeningScene);
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
                        $conversationManager->userSaysToBotAcrossScenes($sceneId, $intentSceneId, $intentNode, $intentIdx);
                    } elseif ($speaker === 'b') {
                        $conversationManager->botSaysToUserAcrossScenes($sceneId, $intentSceneId, $intentNode, $intentIdx);
                    } else {
                        Log::debug("I don't know about the speaker type '{$speaker}'");
                    }
                } else if ($speaker === 'u') {
                    $conversationManager->userSaysToBot($sceneId, $intentNode, $intentIdx);
                } else if ($speaker === 'b') {
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
     * Publish the conversation to DGraph.
     *
     * @param ConversationNode $conversation
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function publishConversation(ConversationNode $conversation)
    {
        $dGraph = app()->make(DGraphClient::class);
        $mutation = new DGraphMutation($conversation);

        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $dGraph->tripleMutation($mutation);
        if ($mutationResponse->getData()['code'] === 'Success') {
            // TODO can't we get this from the response above rather than going back to dgraph?
            $uid = ConversationQueryFactory::getConversationTemplateUid($this->name, $dGraph);

            // Set conversation status to "published".
            $this->status = 'published';
            $this->graph_uid = $uid;
            $this->save(['validate' => false]);

            ConversationStateLog::create([
                'conversation_id' => $this->id,
                'message' => 'Published conversation to DGraph.',
                'type' => 'publish_conversation',
            ])->save();

            return true;
        }

        return false;
    }

    /**
     * Unpublish the conversation from DGraph.
     *
     * @param bool $reValidate
     * @return bool
     */
    public function unPublishConversation($reValidate = true)
    {
        $dGraph = new DGraphClient(env('DGRAPH_URL'), env('DGRAPH_PORT'));

        $uid = ConversationQueryFactory::getConversationTemplateUid($this->name, $dGraph);

        if ($this->graph_uid === $uid) {
            $deleteResponse = $dGraph->deleteNode($uid);

            if ($deleteResponse['code'] === 'Success') {
                // Don't update conversation status if not requested.
                if ($reValidate) {
                    // Set conversation status to "validated".
                    $this->status = 'validated';
                    $this->graph_uid = null;
                    $this->save(['validate' => false]);

                    // Add log message.
                    ConversationStateLog::create([
                        'conversation_id' => $this->id,
                        'message' => 'Unpublished conversation from DGraph.',
                        'type' => 'unpublish_conversation',
                    ])->save();
                }

                return true;
            }
        }

        return false;
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

        if (is_array($intentValue)) {
            $intentLabel = $intentValue['i'];
            $actionLabel = $intentValue['action'] ?? null;
            $interpreterLabel = $intentValue['interpreter'] ?? null;
            $completes = $intentValue['completes'] ?? false;
            $confidence = $intentValue['confidence'] ?? false;
            $intentSceneId = $intent[$speaker]['scene'] ?? null;
            $expectedAttributes = $intent[$speaker]['expected_attributes'] ?? null;
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

        return $intentNode;
    }

    /**
     * @param array $conditions
     * @param ConversationManager $cm
     */
    public function addConversationConditions(array $conditions, ConversationManager $cm)
    {
        foreach ($conditions as $key => $condition) {
            try {
                $conditionObject = $this->createCondition($condition['condition']);
                $cm->addConditionToConversation($conditionObject);
            } catch (Exception $e) {
                Log::debug(
                    sprintf(
                        'Could not create condition because: %s',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @param array $condition
     * @return Condition
     */
    private function createCondition(array $condition)
    {
        $attributeName = isset($condition['attribute']) ? $condition['attribute'] : null;

        // Confirm we have an attribute name to work with.
        if (!isset($attributeName)) {
            throw new ConditionDoesNotDefineAttributeException(
                'Condition found in Yaml model without defined attribute name'
            );
        }

        list($contextId, $attributeId) = ContextParser::determineContextAndAttributeId($attributeName);

        /* @var AttributeResolver $attributeResolver */
        $attributeResolver = resolve(AttributeResolver::class);
        if (!array_key_exists($attributeId, $attributeResolver->getSupportedAttributes())) {
            throw new AttributeIsNotSupported(
                sprintf('Attribute %s could not be resolved', $attributeName)
            );
        }

        $operation = isset($condition['operation']) ? $condition['operation'] : null;
        $value = isset($condition['value']) ? $condition['value'] : null;

        // Now check that we have a valid operation and a value if required for that operation.
        if (isset($operation)) {
            if (in_array($operation, AbstractAttribute::allowedAttributeOperations())) {
                if (!in_array($operation, AbstractAttribute::operationsNotRequiringValue()) && !isset($value)) {
                    throw new ConditionRequiresValueButDoesNotDefineItException(
                        sprintf('Condition %s required a value but has not defined it', $attributeName)
                    );
                }
            } else {
                throw new ConditionDoesNotDefineValidOperationException(
                    sprintf('Condition operation %s is not a valid operation', $operation)
                );
            }
        } else {
            throw new ConditionDoesNotDefineOperationException(
                sprintf('Condition %s does not define an operation', $condition['attribute'])
            );
        }

        $attribute = $attributeResolver->getAttributeFor($attributeId, $value);

        // Now we can create the condition - we set an id as a helper
        $id = sprintf('%s-%s-%s', $attributeName, $operation, $value);
        $condition = new Condition($attribute, $operation, $id);
        $condition->setContextId($contextId);
        Log::debug('Created condition from Yaml.');
        return $condition;
    }

}
