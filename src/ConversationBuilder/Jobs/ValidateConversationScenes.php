<?php

namespace OpenDialogAi\ConversationBuilder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\Jobs\Traits\ValidateConversationTrait;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ValidateConversationScenes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidateConversationTrait;

    /** @var Conversation */
    protected $conversation;

    /** @var string  */
    protected $jobName;

    /**
     * Create a new job instance.
     *
     * @param $conversation
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

        try {
            Yaml::parse($this->conversation->model);
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
