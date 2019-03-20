<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ConversationEngine\Conversation;
use OpenDialogAi\ConversationEngine\ConversationLog;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationEngineTest extends TestCase
{
        public $validYaml = <<<EOT
conversation: create_a_new_checklist
scenes:
  -
    scene: request_new_list
    type: opening
    incoming:
      -
        intent: intent.list.new
        attributes:
          -
            type: list.name
            if-not-present:
              outgoing:
                -
                  intent: intent.list.request_name
                  scene: request_name
          -
            type: list.type
            if-not-present:
              outgoing:
                -
                  intent: intent.list.request_type
                  scene: request_type
        preconditions:
          -
            condition: condition.list.user_authorised
            on_fail:
              outgoing:
                -
                  intent: intent.list.authorise
                  scene: authorisation
        actions:
          -
            action: action.list.create
            on-status: 200
            outgoing:
              -
                intent: intent.list.summary_report
        outgoing:
          -
            intent: intent.list.created_confirmation
            completes: true
  -
    scene: request_type
    incoming:
      -
        intent: intent.list.type
        interpreter: interpreter.list.type
        trigger_intent: intent.list.new
      -
        intent: intent.core.no_match
        outgoing:
          -
            intent: intent.list.type_nomatch_recover
            repeating: true
            max-tries: 2
            completes: true
  -
    scene: request_name
    incoming:
      -
        intent: intent.list.name
        trigger_intent: intent.list.new
        completes: true
      -
        intent: intent.core.no_match
        outgoing:
          -
            intent: intent.list.type_nomatch_recover
            repeating: true
            max-tries: 2
            completes: true
EOT;

    /**
     * Ensure that we can create a conversation.
     */
    public function testConversationDb()
    {
        Conversation::create(['name' => 'Test Conversation', 'model' => 'conversation:']);
        $conversation = Conversation::where('name', 'Test Conversation')->first();
        $this->assertEquals('Test Conversation', $conversation->name);
    }

    /**
     * Ensure that the Conversation relationships work correctly.
     */
    public function testConversationDbRelationships()
    {
        Conversation::create(['name' => 'Test Conversation', 'model' => 'conversation:']);
        $conversation = Conversation::where('name', 'Test Conversation')->first();

        ConversationLog::create([
            'conversation_id' => $conversation->id,
            'message' => 'new revision',
            'type' => 'update',
        ]);
        $conversationLog = ConversationLog::where('message', 'new revision')->first();

        // Ensure we can get a ConversationLog's Conversation.
        $this->assertEquals($conversation->id, $conversationLog->conversation->id);

        // Ensure we can get a Conversation's ConversationLogs.
        $this->assertTrue($conversation->conversationLogs->contains($conversationLog));
    }

    /**
     * Ensure that the model YAML is checked for validity.
     */
    public function testConversationYamlValidation()
    {
        // Assert that invalid yaml is detected.
        Conversation::create(['name' => 'Test Conversation', 'model' => '--- "']);
        $conversation = Conversation::where('name', 'Test Conversation')->first();
        $conversationLog = ConversationLog::where('conversation_id', $conversation->id)->firstOrFail();
        $this->assertStringStartsWith('[] String value found, but an object is required', $conversationLog->message);

        // Assert that no validation log is created for valid yaml.
        $conversation2 = Conversation::create(['name' => 'Test Conversation 2', 'model' => $this->validYaml]);
        $this->assertNull(ConversationLog::where('conversation_id', $conversation2->id)
            ->where('type', 'validate_conversation_yaml')->first());
    }

    /**
     * Ensure that the model YAML schema is checked for validity.
     */
    public function testConversationYamlSchemaValidation()
    {
        // Assert that yaml not matching the schema is detected.
        Conversation::create(['name' => 'Test Conversation', 'model' => "---\nconversation_name: test"]);
        $conversation = Conversation::where('name', 'Test Conversation')->first();
        $conversationLog = ConversationLog::where('conversation_id', $conversation->id)->firstOrFail();
        $this->assertStringStartsWith('[conversation] The property conversation is required', $conversationLog->message);

        // Assert that no validation log is created for a valid schema.
        $conversation2 = Conversation::create(['name' => 'Test Conversation 2', 'model' => $this->validYaml]);
        $this->assertNull(ConversationLog::where('conversation_id', $conversation2->id)
            ->where('type', 'validate_conversation_yaml_schema')->first());
    }

    /**
     * Ensure that revisions are stored for model and notes edits.
     */
    public function testConversationRevisionCreation()
    {
        $conversation = Conversation::create(['name' => 'Test Conversation', 'model' => "---\nconversation_name: test"]);
        $conversation->notes = 'test notes';
        $conversation->save();
        $this->assertEquals('notes', $conversation->revisionHistory[0]->fieldName());
        $this->assertEquals('', $conversation->revisionHistory[0]->oldValue());
        $this->assertEquals('test notes', $conversation->revisionHistory[0]->newValue());

        $conversation2 = Conversation::create(['name' => 'Test Conversation 2', 'model' => "---\nconversation_name: test"]);
        $conversation2->model = "---\nconversation: test";
        $conversation2->save();
        $this->assertEquals('model', $conversation2->revisionHistory[0]->fieldName());
        $this->assertEquals("---\nconversation_name: test", $conversation2->revisionHistory[0]->oldValue());
        $this->assertEquals("---\nconversation: test", $conversation2->revisionHistory[0]->newValue());
    }

    /**
     * Ensure that revisions are not stored for status changes.
     */
    public function testConversationRevisionNonCreation()
    {
        $conversation = Conversation::create(['name' => 'Test Conversation', 'model' => "---\nconversation: test"]);
        $conversation->status = 'published';
        $conversation->save();
        $this->assertEmpty($conversation->revisionHistory);
    }
}
