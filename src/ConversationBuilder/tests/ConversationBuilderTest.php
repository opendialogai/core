<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\ConversationQueryFactory;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class ConversationBuilderTest extends TestCase
{
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

        ConversationStateLog::create([
            'conversation_id' => $conversation->id,
            'message' => 'new revision',
            'type' => 'update',
        ]);
        $conversationStateLog = ConversationStateLog::where('message', 'new revision')->first();

        // Ensure we can get a ConversationStateLog's Conversation.
        $this->assertEquals($conversation->id, $conversationStateLog->conversation->id);

        // Ensure we can get a Conversation's ConversationStateLogs.
        $this->assertTrue($conversation->conversationStateLogs->contains($conversationStateLog));
    }

    /**
     * Ensure that the model YAML is checked for validity.
     */
    public function testConversationYamlValidation()
    {
        // Assert that invalid yaml is detected.
        Conversation::create(['name' => 'Test Conversation', 'model' => '--- "']);
        $conversation = Conversation::where('name', 'Test Conversation')->first();
        $conversationStateLog = ConversationStateLog::where('conversation_id', $conversation->id)->firstOrFail();
        $this->assertStringStartsWith('[] String value found, but an object is required', $conversationStateLog->message);

        // Assert that no validation log is created for valid yaml.
        $conversation2 = Conversation::create(['name' => 'Test Conversation 2', 'model' => $this->conversation1()]);
        $this->assertNull(ConversationStateLog::where('conversation_id', $conversation2->id)
            ->where('type', 'validate_conversation_yaml')->first());
    }

    /**
     * Ensure that the model YAML schema is checked for validity.
     */
    public function testConversationYamlSchemaValidation()
    {
        // Assert that yaml not matching the schema is detected.
        Conversation::create(['name' => 'test', 'model' => "---\nconversation_name: test"]);
        $conversation = Conversation::where('name', 'test')->first();
        $conversationStateLog = ConversationStateLog::where('conversation_id', $conversation->id)->firstOrFail();
        $this->assertStringStartsWith('[conversation] The property conversation is required', $conversationStateLog->message);

        // Assert that no validation log is created for a valid schema.
        $conversation2 = Conversation::create(['name' => 'hello_bot_world', 'model' => $this->conversation1()]);
        $this->assertNull(ConversationStateLog::where('conversation_id', $conversation2->id)
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
     * Ensure that logs/revisions are cleaned up when Conversations are deleted.
     */
    public function testConversationDeletion()
    {
        $conversation = Conversation::create(['name' => 'hello_bot_world', 'model' => $this->conversation1()]);

        $conversationStateLog = ConversationStateLog::create([
            'conversation_id' => $conversation->id,
            'message' => 'new revision',
            'type' => 'update',
        ]);

        $conversationStateLogs = ConversationStateLog::where('conversation_id', $conversation->id)->get();
        $this->assertCount(1, $conversationStateLogs);

        $activities = Activity::where('subject_id', $conversation->id)->get();
        $this->assertCount(1, $activities);

        $conversation->delete();

        $conversationStateLogs = ConversationStateLog::where('conversation_id', $conversation->id)->get();
        $this->assertCount(0, $conversationStateLogs);

        $activities = Activity::where('subject_id', $conversation->id)->get();
        $this->assertCount(0, $activities);
    }

    /**
     * Ensure that a conversation representation can be made from a YAML file.
     *
     * @grpu
     */
    public function testConversationRepresentationCreation()
    {
        $conversation = Conversation::create(['name' => 'Test Conversation', 'model' => $this->conversation1()]);

        /* @var AttributeResolver $attributeResolver */
        $attributeResolver = $this->app->make(AttributeResolver::class);
        $attributes = ['test' => IntAttribute::class];
        $attributeResolver->registerAttributes($attributes);

        /* @var \OpenDialogAi\Core\Conversation\Conversation $conversationModel */
        $conversationModel = $conversation->buildConversation();
        $this->assertInstanceOf('OpenDialogAi\Core\Conversation\Conversation', $conversationModel);

        // There should be two conditions
        $this->assertCount(2, $conversationModel->getConditions());

        // There should be two scenes
        $this->assertCount(4, $conversationModel->getAllScenes());

        // The opening scene should be called opening_scene
        $this->assertEquals('opening_scene', $conversationModel->getScene('opening_scene')->getId());

        // There should be one opening scene
        $this->assertCount(1, $conversationModel->getOpeningScenes());

        /* @var Scene $openingScene */
        $openingScene = $conversationModel->getOpeningScenes()->first()->value;

        // The opening scene should have three intents
        $this->assertCount(3, $openingScene->getAllIntents());

        // User says one thing
        $this->assertCount(1, $openingScene->getIntentsSaidByUser());
        $this->assertEquals('hello_bot', $openingScene->getIntentsSaidByUser()->first()->value->getId());
        // Bot replies
        $this->assertCount(2, $openingScene->getIntentsSaidByBot());
        $this->assertEquals('hello_user', $openingScene->getIntentsSaidByBot()->first()->value->getId());

        // Intents should have actions and interpreters
        /* @var Intent $userIntent */
        $userIntent = $openingScene->getIntentsSaidByUser()->first()->value;
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());

        /* @var Intent $botIntent */
        $botIntent = $openingScene->getIntentsSaidByBot()->first()->value;
        $this->assertTrue($botIntent->causesAction());
        $this->assertFalse($botIntent->hasInterpreter());

        /* @var Scene $scene2 */
        $scene2 = $conversationModel->getNonOpeningScenes()->first()->value;

        // Scene two should be called scene2
        $this->assertEquals('scene2', $conversationModel->getScene('scene2')->getId());

        // The scene2 should have two intents
        $this->assertCount(2, $scene2->getAllIntents());

        // User says one thing
        $this->assertCount(1, $scene2->getIntentsSaidByUser());
        $this->assertEquals('how_are_you', $scene2->getIntentsSaidByUser()->first()->value->getId());
        // Bot replies
        $this->assertCount(1, $scene2->getIntentsSaidByBot());
        $this->assertEquals('doing_dandy', $scene2->getIntentsSaidByBot()->first()->value->getId());
    }

    /**
     * Ensure that a conversation representation can be persisted to DGraph.
     */
    public function testConversationRepresentationPersist()
    {
        $this->publishConversation($this->conversation1());

        /** @var DGraphClient $client */
        $client = $this->app->make(DGraphClient::class);

        $template = ConversationQueryFactory::getConversationFromDGraphWithTemplateName(
            'hello_bot_world',
            $client
        );

        $this->assertEquals('hello_bot_world', $template->getId());
    }
}
