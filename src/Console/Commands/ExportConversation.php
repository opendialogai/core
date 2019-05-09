<?php

namespace OpenDialogAi\Core\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ExportConversation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:conversation {conversation name} {--f|filename=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a conversation and its intents + outgoing messages';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Load the conversation.
        $conversationName = $this->argument('conversation name');
        $conversation = Conversation::where(['name' => $conversationName])->first();

        if (!$conversation) {
            $this->error(sprintf('I could not find a conversation with name %s !', $conversationName));
            exit;
        }

        $this->info(sprintf('Exporting conversation with id %s', $conversation->id));

        // Find this conversation's intents.
        $outgoingIntents = [];
        $parsedConversation = $conversation->buildConversation();
        /** @var Intent $intent */
        foreach ($parsedConversation->getAllIntents() as $intent) {
            $outgoingIntent = OutgoingIntent::where('name', $intent->getLabel())->with('messageTemplates')->first();
            if ($outgoingIntent && !isset($outgoingIntents[$outgoingIntent->id])) {
                $outgoingIntents[$outgoingIntent->id] = $outgoingIntent;
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
