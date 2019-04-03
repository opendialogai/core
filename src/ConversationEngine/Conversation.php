<?php

namespace OpenDialogAi\ConversationEngine;

use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationScenes;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationModel;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationYaml;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationYamlSchema;
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
    public function conversationlogs()
    {
        return $this->hasMany('OpenDialogAi\ConversationEngine\ConversationLog');
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
            \Log::debug('Could not parse converation yaml!');
        }

        $cm = new ConversationManager($yaml['id']);

        foreach ($yaml['scenes'] as $sceneId => $scene) {
            $sceneIsOpeningScene = $sceneId === 'opening_scene';
            $cm->createScene($sceneId, $sceneIsOpeningScene);

            $intents = [];

            $intentIdx = 0;
            foreach ($scene['intents'] as $speaker => $intentName) {
                $intent = new Intent($intentName);

                if ($speaker === 'u') {
                    $cm->userSaysToBot($sceneId, $intent, $intentIdx);
                } else if ($speaker === 'b') {
                    $cm->botSaysToUser($sceneId, $intent, $intentIdx);
                } else {
                    \Log::debug("I don't know about the speaker type '{$speaker}'");
                }

                $intentIdx++;
            }
        }

        return $cm->getConversation();
    }
}
