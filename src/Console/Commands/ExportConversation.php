<?php

namespace OpenDialogAi\Core\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Conversation as CoreConversation;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ExportConversation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:conversation {conversation id} {--f|filename=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a conversation and its intents + outgoing messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Load the conversation.
        $conversationId = $this->argument('conversation id');
        $conversation = Conversation::findOrFail($conversationId);

        // Find this conversation's intents.
        $outgoingIntents = [];
        $parsedConversation = $conversation->buildConversation();
        foreach ($parsedConversation->getAllIntents() as $intent) {
            if ($outgoingIntent = OutgoingIntent::where('name', $intent->getLabel())->with('messageTemplates')->first()) {
                if (!isset($outgoingIntents[$outgoingIntent->id])) {
                    $outgoingIntents[$outgoingIntent->id] = $outgoingIntent;
                }
            }
        }

        $output = serialize([
            'conversation' => $conversation,
            'outgoingIntents' => $outgoingIntents,
        ]);

        $filename = $this->option('filename');

        if ($filename) {
            file_put_contents($filename, $output);
        } else {
            print($output);
        }
    }
}
