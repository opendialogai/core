<?php

namespace OpenDialogAi\ConversationEngine\Jobs;

use OpenDialogAi\ConversationEngine\Conversation;
use OpenDialogAi\ConversationEngine\ConversationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use JsonSchema\Validator;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ValidateConversationYamlSchema implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $conversation;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * Execute the job.
     *
     * We are checking whether the conversation model is valid YAML.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->conversation->status === 'invalid') {
            // Delete the job so that it will not be re-tried.
            $this->delete();

            // Update this job's status.
            $this->conversation->yaml_schema_validation_status = 'invalid';
            $this->conversation->save(['validate' => false]);
            return;
        }

        $status = 'validated';

        try {
            $model = Yaml::parse($this->conversation->model, Yaml::PARSE_OBJECT_FOR_MAP);
        } catch (ParseException $exception) {
            // Log a validation message with the error.
            $log = new ConversationLog();
            $log->conversation_id = $this->conversation->id;
            $log->message = $exception->getMessage();
            $log->type = 'validate_conversation_yaml_schema';
            $log->save();

            // Set validation status.
            $status = 'invalid';
        }

        // Now we check the model schema.
        if ($status === 'validated') {
            // Validate against our JSON schema.
            $validator = new Validator();
            $reflector = new ReflectionClass(get_class($this));
            $dir = dirname($reflector->getFileName());
            $validator->validate(
                $model,
                (object)['$ref' => 'file://' . $dir . '/conversation.schema.json']
            );
            if ($validator->isValid()) {
                // Save the name if the model validates.
                $this->conversation->name = $model->conversation;
            } else {
                // Mark as invalid.
                $status = 'invalid';

                foreach ($validator->getErrors() as $error) {
                    // Log a validation message with the error.
                    $log = new ConversationLog();
                    $log->conversation_id = $this->conversation->id;
                    $log->message = sprintf("[%s] %s\n", $error['property'], $error['message']);
                    $log->type = 'validate_conversation_yaml_schema';
                    $log->save();
                }
            }
        }

        $this->conversation->yaml_schema_validation_status = $status;

        if ($status === 'invalid') {
            // Delete the job so that it will not be re-tried.
            $this->delete();

            // Update the conversation status.
            $this->conversation->status = 'invalid';
        }

        $this->conversation->save(['validate' => false]);
    }
}
