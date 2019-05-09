<?php

namespace OpenDialogAi\ConversationBuilder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonSchema\Validator;
use OpenDialogAi\ConversationBuilder\Jobs\Traits\ValidateConversationTrait;
use ReflectionClass;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ValidateConversationYamlSchema implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidateConversationTrait;

    // Validation job name.
    protected $jobName;

    /**
     * Create a new job instance.
     *
     * @param $conversation
     */
    public function __construct($conversation)
    {
        $this->conversation = $conversation;
        $this->jobName = 'yaml_schema_validation_status';
    }

    /**
     * Execute the job.
     *
     * We are checking whether the conversation model is valid YAML.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
        if (!$this->checkConversationStatus()) {
            return;
        }

        $status = 'validated';

        try {
            $model = Yaml::parse($this->conversation->model, Yaml::PARSE_OBJECT_FOR_MAP);
        } catch (ParseException $exception) {
            // Log a validation message with the error.
            $this->logMessage($this->conversation->id, 'validate_conversation_yaml_schema', $exception->getMessage());

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
                // Ensure that the name matches.
                if ($model->conversation->id !== $this->conversation->name) {
                    // Mark as invalid.
                    $status = 'invalid';

                    // Log a validation message with the error.
                    $this->logMessage(
                        $this->conversation->id,
                        'validate_conversation_yaml_schema',
                        sprintf(
                            "Name \"%s\" in Yaml does not match conversation name \"%s\"\n",
                            $model->conversation->id,
                            $this->conversation->name
                        )
                    );
                }
            } else {
                // Mark as invalid.
                $status = 'invalid';

                foreach ($validator->getErrors() as $error) {
                    // Log a validation message with the error.
                    $this->logMessage(
                        $this->conversation->id,
                        'validate_conversation_yaml_schema',
                        sprintf("[%s] %s\n", $error['property'], $error['message'])
                    );
                }
            }
        }

        $this->conversation->{$this->jobName} = $status;

        if ($status === 'invalid') {
            // Delete the job so that it will not be re-tried.
            $this->delete();

            // Update the conversation status.
            $this->conversation->status = 'invalid';
        }

        $this->conversation->save(['validate' => false]);
    }
}
