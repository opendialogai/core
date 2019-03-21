<?php

namespace OpenDialogAi\ConversationEngine;

use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationScenes;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationModel;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationYaml;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationYamlSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;


class Conversation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'model',
        'notes',
    ];

    protected static $logAttributes = ['model', 'notes'];

    protected static $logName = 'conversation_log';

    protected static $logOnlyDirty = true;

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
}
