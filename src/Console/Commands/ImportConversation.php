<?php

namespace OpenDialogAi\Core\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ImportConversation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversation:import {filename} {--publish} {--yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a conversation and its intents + outgoing messages';

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
        $data = unserialize(file_get_contents($this->argument('filename')));
        if (!is_array($data) || !isset($data['conversation'])) {
            $this->error('Sorry, I could not read that file!');
            exit;
        }

        $deletedModelText = '';
        $existingModelText = '';
        $newModelText = '';
        $unmatchedMessageTemplateIds = [];

        // Check if there is an existing conversation with this name.
        if ($existingConversation = Conversation::where('name', $data['conversation']->name)->first()) {
            $existingModelText .=
                "* Conversation with ID " . $existingConversation->id . " and name " . $existingConversation->name . "\n";
        } else {
            $newModelText .=
                "* Conversation with name " . $data['conversation']->name . "\n";
        }

        // Check for existing intents with this name.
        foreach ($data['outgoingIntents'] as $outgoingIntent) {
            if ($existingIntent = OutgoingIntent::where('name', $outgoingIntent->name)->first()) {
                $existingModelText .=
                    "* Outgoing Intent with ID " . $existingIntent->id . " and name " . $existingIntent->name . "\n";
                foreach ($existingIntent->messageTemplates as $messageTemplate) {
                    $unmatchedMessageTemplateIds[] = $messageTemplate->id;
                }
            } else {
                $newModelText .=
                    "* Outgoing Intent with name " . $outgoingIntent->name . "\n";
            }

            foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
                if ($existingMessageTemplate = MessageTemplate::where('name', $messageTemplate->name)->first()) {
                    if (($key = array_search($existingMessageTemplate->id, $unmatchedMessageTemplateIds)) !== false) {
                        unset($unmatchedMessageTemplateIds[$key]);
                    }

                    $existingModelText .=
                        "* Message Template with ID " . $existingMessageTemplate->id .
                        " and name " . $existingMessageTemplate->name . "\n";
                } else {
                    $newModelText .=
                        "* Message Template with name " . $messageTemplate->name . "\n";
                }
            }

            foreach ($unmatchedMessageTemplateIds as $unmatchedMessageTemplateId) {
                $existingMessageTemplate = MessageTemplate::find($unmatchedMessageTemplateId);
                $deletedModelText .=
                    "* Message Template with ID " . $existingMessageTemplate->id .
                    " and name " . $existingMessageTemplate->name . "\n";
            }
        }

        // Show a message and get confirmation.
        $messageText = '';
        if ($newModelText) {
            $messageText .= "\nThe following items will be CREATED:\n\n" . $newModelText . "\n";
        }
        if ($existingModelText) {
            $messageText .= "The following items will be OVERWRITTEN:\n\n" . $existingModelText . "\n\n";
        }
        if ($deletedModelText) {
            $messageText .= "The following items will be DELETED:\n\n" . $deletedModelText . "\n\n";
        }
        $messageText .= "Do you wish to continue?";

        // Confirm proceeding if the --yes flag was not given.
        if (!$this->option('yes') && !$this->confirm($messageText)) {
            exit;
        }

        // Import the models.
        $attributes = [];
        foreach ($data['conversation']->getFillable() as $attribute) {
            $attributes[$attribute] = $data['conversation']->{$attribute};
        }

        /** @var Conversation $newConversation */
        $this->info(sprintf('Adding/updating conversation with name %s', $data['conversation']->name));
        $newConversation = Conversation::updateOrCreate(['name' => $data['conversation']->name], $attributes);
        if ($this->option('publish')) {
            $this->info(sprintf('Publishing conversation with name %s', $newConversation->name));
            $newConversation->publishConversation($newConversation->buildConversation());
        }

        foreach ($data['outgoingIntents'] as $outgoingIntent) {
            $attributes = [];
            foreach ($outgoingIntent->getFillable() as $attribute) {
                $attributes[$attribute] = $outgoingIntent->{$attribute};
            }

            $this->info(sprintf('Adding/updating intent with name %s', $outgoingIntent->name));
            $newIntent = OutgoingIntent::updateOrCreate(['name' => $outgoingIntent->name], $attributes);

            foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
                $attributes = [];
                foreach ($messageTemplate->getFillable() as $attribute) {
                    $attributes[$attribute] = $messageTemplate->{$attribute};
                }
                $attributes['outgoing_intent_id'] = $newIntent->id;

                $this->info(sprintf('Adding/updating message template with name %s', $messageTemplate->name));
                MessageTemplate::updateOrCreate(['name' => $messageTemplate->name], $attributes);
            }

            foreach ($unmatchedMessageTemplateIds as $unmatchedMessageTemplateId) {
                $existingMessageTemplate = MessageTemplate::find($unmatchedMessageTemplateId);
                $this->info(sprintf('Deleting message template with name %s', $existingMessageTemplate->name));
                $existingMessageTemplate->delete();
            }
        }
    }
}
