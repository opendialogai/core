<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationLog;
use OpenDialogAi\Core\Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class ConversationBuilderTest extends TestCase
{
        public $validYaml = <<<EOT
conversation:
  id: hello_bot_world
  scenes:
    opening_scene:
      intents:
        - u: hello_bot
        - b: hello_user
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
        $activity = Activity::where('log_name', 'conversation_log')->get()->last();
        $this->assertArrayHasKey('notes', $activity->changes['attributes']);
        $this->assertEquals('', $activity->changes['old']['notes']);
        $this->assertEquals('test notes', $activity->changes['attributes']['notes']);

        $conversation2 = Conversation::create(['name' => 'Test Conversation 2', 'model' => "---\nconversation_name: test"]);
        $conversation2->model = "---\nconversation: test";
        $conversation2->save();
        $activity = Activity::where('log_name', 'conversation_log')->get()->last();
        $this->assertArrayHasKey('model', $activity->changes['attributes']);
        $this->assertEquals("---\nconversation_name: test", $activity->changes['old']['model']);
        $this->assertEquals("---\nconversation: test", $activity->changes['attributes']['model']);
    }

    /**
     * Ensure that revisions are not stored for status changes.
     */
    public function testConversationRevisionNonCreation()
    {
        $conversation = Conversation::create(['name' => 'Test Conversation', 'model' => "---\nconversation: test"]);
        $conversation->status = 'published';
        $conversation->save();
        $activity = Activity::where('log_name', 'conversation_log')->get()->last();
        $this->assertArrayNotHasKey('status', $activity->changes['attributes']);
    }

    /**
     * Ensure that a conversation representation can be made from a YAML file.
     */
    public function testConversationRepresentationCreation()
    {
        $conversation = Conversation::create(['name' => 'Test Conversation', 'model' => $this->validYaml]);
        $conversationModel = $conversation->buildConversation();

        $this->assertInstanceOf('OpenDialogAi\Core\Conversation\Conversation', $conversationModel);
    }

    /**
     * Ensure that a conversation representation can be persisted to DGraph.
     */
    public function testConversationRepresentationPersist()
    {
        if (getenv('LOCAL') !== true) {
            $this->markTestSkipped('This test only runs on local environments.');
        }

        $conversation = Conversation::create(['name' => 'Test Conversation', 'model' => $this->validYaml]);
        $conversationModel = $conversation->buildConversation();

        // Assert that we think publishing was successful.
        $this->assertTrue($conversation->publishConversation($conversationModel));

        /**
         * TODO: Assert that the conversation exists in DGraph.
        $dGraph = new DGraphClient(env('DGRAPH_URL'), env('DGRAPH_PORT'));
        $query = new DGraphQuery();
        $query->allofterms('ei_type', ['conversation'])
            ->setQueryGraph(['id' => 'hello_bot_world']);
        */
    }
}
