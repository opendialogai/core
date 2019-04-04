<?php

namespace OpenDialogAi\ConversationEngine\Jobs;

use \Exception;
use OpenDialogAi\ConversationEngine\Conversation;
use OpenDialogAi\ConversationEngine\ConversationLog;
use OpenDialogAi\ConversationEngine\Jobs\Traits\ValidateConversationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ValidateConversationModel implements ShouldQueue
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
          $this->jobName = 'model_validation_status';
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
            $log = new ConversationLog();
            $log->conversation_id = $this->conversation->id;
            $log->message = $exception->getMessage();
            $log->type = 'validate_conversation_model';
            $log->save();

            // Set validation status.
            $status = 'invalid';
        } finally {
            $this->conversation->{$this->jobName} = $status;

            if ($status === 'invalid') {
                // Delete the job so that it will not be re-tried.
                $this->delete();

                // Update the conversation status.
                $this->conversation->status = 'invalid';
            } else {
                // Update the conversation status.
                $this->conversation->status = 'validated';
            }

            $this->conversation->save(['validate' => false]);
        }
    }
}
