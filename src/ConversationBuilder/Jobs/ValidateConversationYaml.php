<?php

namespace OpenDialogAi\ConversationBuilder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\Jobs\Traits\ValidateConversationTrait;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ValidateConversationYaml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidateConversationTrait;

    /** @var Conversation */
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
        $status = 'validated';

        try {
            Yaml::parse($this->conversation->model);
        } catch (ParseException $exception) {
            // Log a validation message with the error.
            $this->logMessage($this->conversation->id, 'validate_conversation_yaml', $exception->getMessage());

            // Set validation status.
            $status = 'invalid';
        } finally {
            $this->conversation->yaml_validation_status = $status;
            if ($status === 'invalid') {
                // Delete the job so that it will not be re-tried.
                $this->delete();

                // Update the conversation status.
                $this->conversation->status = ConversationNode::SAVED;
            }
            $this->conversation->save(['validate' => false]);
        }
    }
}
