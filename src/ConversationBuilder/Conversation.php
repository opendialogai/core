<?php

namespace OpenDialogAi\ConversationBuilder;

use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Interpreter;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphMutation;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationScenes;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationModel;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationYaml;
use OpenDialogAi\ConversationBuilder\Jobs\ValidateConversationYamlSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

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
     * @return OpenDialogAi\Core\Conversation\Conversation
     */
    public function buildConversation()
    {
        try {
            $yaml = Yaml::parse($this->model)['conversation'];
        } catch (ParseException $exception) {
            Log::debug('Could not parse converation yaml!');
        }

        $cm = new ConversationManager($yaml['id']);

        // Build the conversation in two steps. First all the scenes and then all the intents as
        // intents may connect between scenes.
        foreach ($yaml['scenes'] as $sceneId => $scene) {
            $sceneIsOpeningScene = $sceneId === 'opening_scene';
            $cm->createScene($sceneId, $sceneIsOpeningScene);
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
                        $cm->userSaysToBotAcrossScenes($sceneId, $intentSceneId, $intentNode, $intentIdx);
                    } elseif ($speaker === 'b') {
                        $cm->botSaysToUserAcrossScenes($sceneId, $intentSceneId, $intentNode, $intentIdx);
                    } else {
                        Log::debug("I don't know about the speaker type '{$speaker}'");
                    }
                } else {
                    if ($speaker === 'u') {
                        $cm->userSaysToBot($sceneId, $intentNode, $intentIdx);
                    } elseif ($speaker === 'b') {
                        $cm->botSaysToUser($sceneId, $intentNode, $intentIdx);
                    } else {
                        Log::debug("I don't know about the speaker type '{$speaker}'");
                    }
                }

                $intentIdx++;
            }
        }

        return $cm->getConversation();
    }

    /**
     * Publish the conversation to DGraph.
     *
     * @return bool
     */
    public function publishConversation(\OpenDialogAi\Core\Conversation\Conversation $conversation)
    {
        $dGraph = new DGraphClient(env('DGRAPH_URL'), env('DGRAPH_PORT'));
        $mutation = new DGraphMutation($conversation);

        /* @var DGraphMutationResponse $mutationResponse */
        $mutationResponse = $dGraph->tripleMutation($mutation);
        if ($mutationResponse->getData()['code'] === 'Success') {
            // Set conversation status to "published".
            $this->status = 'published';
            $this->save(['validate' => false]);

            // Add log message.
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
     * @return bool
     */
    public function unPublishConversation($reValidate = true)
    {
        // TODO: Actually remove conversation from DGraph.
        // $dGraph = new DGraphClient(env('DGRAPH_URL'), env('DGRAPH_PORT'));

        // Don't update conversation status if not requested.
        if ($reValidate) {
            // Set conversation status to "validated".
            $this->status = 'validated';
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

    /**
     * @param $intent
     * @return Intent
     */
    private function createIntent($intent, &$speaker, &$intentSceneId)
    {
        $speaker = array_keys($intent)[0];
        $intentValue = $intent[$speaker];

        $actionLabel = null;
        $interpreterLabel = null;
        $completes = false;

        if (is_array($intentValue)) {
            $intentLabel = $intentValue['i'];
            $actionLabel = isset($intentValue['action']) ? $intentValue['action'] : null;
            $interpreterLabel = isset($intentValue['interpreter']) ? $intentValue['interpreter'] : null;
            $completes = isset($intentValue['completes']) ? $intentValue['completes'] : false;
            $intentSceneId = isset($intent[$speaker]['scene']) ? $intent[$speaker]['scene'] : null;
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

        return $intentNode;
    }

}