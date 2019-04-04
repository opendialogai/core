<?php

namespace OpenDialogAi\ConversationEngine\Jobs;

use \Exception;
use OpenDialogAi\ConversationEngine\Conversation;
use OpenDialogAi\ConversationEngine\Jobs\Traits\ValidateConversationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ValidateConversationScenes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidateConversationTrait;

    // Conversation object.
    protected $conversation;

    // Validation job name.
    protected $jobName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($conversation)
    {
        $this->conversation = $conversation;
        $this->jobName = 'scenes_validation_status';
    }

    /**
     * Execute the job.
     *
     * We are checking whether all conversation elements are provided
     * by the application.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->checkConversationStatus()) {
            return;
        }

        $status = 'validated';
        $model = [];

        try {
            $model = Yaml::parse($this->conversation->model);
        } catch (ParseException $exception) {
            // Log a validation message with the error.
            $this->logMessage($this->conversation->id, 'validate_conversation_scenes', $exception->getMessage());

            // Set validation status.
            $status = 'invalid';
        } finally {
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
}
