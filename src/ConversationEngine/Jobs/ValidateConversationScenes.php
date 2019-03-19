<?php

namespace OpenDialogAi\ConversationEngine\Jobs;

use \Exception;
use OpenDialogAi\ConversationEngine\Conversation;
use OpenDialogAi\ConversationEngine\ConversationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ValidateConversationScenes implements ShouldQueue
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
     * We are checking whether all conversation elements are provided
     * by the application.
     *
     * @return void
     */
    public function handle()
    {
        $status = 'validated';
        $model = [];

        try {
            $model = Yaml::parse($this->conversation->model);
        } catch (ParseException $exception) {
            // Log a validation message with the error.
            $log = new ConversationLog();
            $log->conversation_id = $this->conversation->id;
            $log->message = $exception->getMessage();
            $log->type = 'validate_conversation_scenes';
            $log->save();

            // Set validation status.
            $status = 'invalid';
        } finally {
            $this->conversation->scenes_validation_status = $status;
            $this->conversation->save(['validate' => false]);

            if ($status === 'invalid') {
                // Fail the job so that the next validation step will not be attempted.
                $this->fail();

                // Delete the job so that it will not be re-tried.
                $this->delete();
            }
        }
    }
}
