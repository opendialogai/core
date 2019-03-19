<?php

namespace OpenDialogAi\ConversationEngine;

use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationScenes;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationModel;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationYaml;
use OpenDialogAi\ConversationEngine\Jobs\ValidateConversationYamlSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;


class Conversation extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;

    protected $fillable = [
        'name',
        'model',
    ];

    protected $keepRevisionOf = ['model', 'notes'];

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
        $do_validation = (!isset($options['validate']) || $options['validate'] === true);

        // Reset validation status.
        if ($do_validation) {
            $this->status = 'imported';
            $this->yaml_validation_status = 'waiting';
            $this->yaml_schema_validation_status = 'waiting';
            $this->scenes_validation_status = 'waiting';
            $this->model_validation_status = 'waiting';
        }

        parent::save($options);

        // Create validation jobs.
        if ($do_validation) {
            ValidateConversationYaml::dispatch($this)->chain([
              new ValidateConversationYamlSchema($this),
              new ValidateConversationScenes($this),
              new ValidateConversationModel($this)
            ]);
        }
    }
}
