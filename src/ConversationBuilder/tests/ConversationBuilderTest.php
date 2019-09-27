<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationBuilder\ConversationStateLog;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelToGraphConverter;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\DGraph\DGraphQueryResponse;
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
        $conversation->save(["validate" => false]);
        $activity = Activity::where('log_name', 'conversation_log')->get()->last();
        $this->assertArrayHasKey('notes', $activity->changes['attributes']);
        $this->assertEquals('', $activity->changes['old']['notes']);
        $this->assertEquals('test notes', $activity->changes['attributes']['notes']);

        $conversation2 = Conversation::create(['name' => 'Test Conversation 2', 'model' => "---\nconversation_name: test"]);
        $conversation2->model = "---\nconversation: test";
        $conversation2->save(["validate" => false]);
        $activity = Activity::where('log_name', 'conversation_log')->get()->last();
        $this->assertArrayHasKey('model', $activity->changes['attributes']);
        $this->assertEquals("---\nconversation_name: test", $activity->changes['old']['model']);
        $this->assertEquals("---\nconversation: test", $activity->changes['attributes']['model']);
    }

    /**
     * Ensure that logs/revisions are cleaned up when Conversations are deleted.
     */
    public function testConversationDeletion()
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::create(['name' => 'hello_bot_world', 'model' => $this->conversation1()]);

        $conversationStateLog = ConversationStateLog::create([
            'conversation_id' => $conversation->id,
            'message' => 'new revision',
            'type' => 'update',
        ]);

        $conversationStateLogs = ConversationStateLog::where('conversation_id', $conversation->id)->get();
        $this->assertCount(1, $conversationStateLogs);

        $activities = Activity::where('subject_id', $conversation->id)->get();
        $this->assertCount(2, $activities);

        $conversation->publishConversation($conversation->buildConversation());
        $conversation->unPublishConversation();
        $conversation->archiveConversation();
        $this->assertTrue($conversation->delete());

        $conversationStateLogs = ConversationStateLog::where('conversation_id', $conversation->id)->get();
        $this->assertCount(0, $conversationStateLogs);

        $activities = Activity::where('subject_id', $conversation->id)->get();
        $this->assertCount(0, $activities);
    }

    public function testConversationPublishedDeletion()
    {
        $this->publishConversation($this->conversation1());

        $conversation = Conversation::where('name', 'hello_bot_world')->first();

        $this->assertEquals($conversation->status, ConversationNode::ACTIVATED);

        $conversation->delete();
    }

    /**
     * Ensure that a conversation representation can be made from a YAML file.
     *
     * @grpu
     */
    public function testConversationRepresentationCreation()
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::create(['name' => 'Test Conversation', 'model' => $this->conversation1()]);

        $attributes = ['test' => IntAttribute::class];
        AttributeResolver::registerAttributes($attributes);

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

        /* @var EIModelConversation $template */
        $conversationStore = app()->make(ConversationStoreInterface::class);
        $conversationConverter = app()->make(EIModelToGraphConverter::class);

        $conversationModel = $conversationStore->getEIModelConversationTemplate('hello_bot_world');
        $conversation = $conversationConverter->convertConversation($conversationModel);

        $this->assertEquals('hello_bot_world', $conversation->getId());
    }

    public function testLogNewConversationVersion()
    {
        $this->publishConversation($this->conversation1());

        /** @var Conversation $conversation */
        $conversation = Conversation::where('name', 'hello_bot_world')->first();

        // Ensure that the initial version + validation & publishing was logged
        $this->assertCount(5, Activity::all());

        /** @var Activity $activity */
        $activity = Activity::all()->last();

        $changedAttributes = $activity->changes['attributes'];
        $this->assertEquals(1, $changedAttributes['version_number']);

        $conversation->model = $this->conversation1() . " ";
        $conversation->save();

        // Ensure that that a new version was not logged (we only want to when we re-activate a conversation).
        // We are expecting two additional items from the validation process (one for the status being reverted to
        // saved and another for it being put back to activated)
        $this->assertCount(7, Activity::all());

        /** @var Activity $activity */
        $activity = Activity::all()->last();

        $changedAttributes = $activity->changes['attributes'];
        $this->assertEquals(1, $changedAttributes['version_number']);
        $this->assertEquals($this->conversation1() . " ", $changedAttributes['model']);

        $conversation->publishConversation($conversation->buildConversation());
        $this->assertEquals(2, $conversation->version_number);

        // Ensure that the new version was logged
        $this->assertCount(8, Activity::all());

        /** @var Activity $activity */
        $activity = Activity::all()->last();
        $changedAttributes = $activity->changes['attributes'];

        $this->assertEquals(2, $changedAttributes['version_number']);
    }

    public function testDeactivating() {
        $this->publishConversation($this->conversation1());

        /** @var DGraphQuery $query */
        $query = new DGraphQuery();
        $query->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->filterEq('id', 'hello_bot_world')
            ->setQueryGraph(DGraphConversationQueryFactory::getConversationTemplateQueryGraph());

        /** @var DGraphClient $client */
        $client = app()->make(DGraphClient::class);

        /** @var DGraphQueryResponse $response */
        $response = $client->query($query);

        // There should only be one conversation in DGraph with this name and it should be marked as 'activated'
        $this->assertCount(1, $response->getData());

        /** @var EIModelCreator $eiModelCreator */
        $eiModelCreator = app()->make(EIModelCreator::class);

        /* @var EIModelConversation $model */
        $model = $eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);

        $this->assertEquals(ConversationNode::ACTIVATED, $model->getConversationStatus());

        // Deactivate the conversation

        /** @var Conversation $conversation */
        $conversation = Conversation::where('name', 'hello_bot_world')->first();

        $this->assertTrue($conversation->unPublishConversation());

        // Re-query
        $response = $client->query($query);
        $this->assertCount(1, $response->getData());
        $model = $eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);
        $this->assertEquals(ConversationNode::DEACTIVATED, $model->getConversationStatus());

    }

    public function testArchiving() {
        $this->publishConversation($this->conversation1());

        // Deactivate the conversation

        /** @var Conversation $conversation */
        $conversation = Conversation::where('name', 'hello_bot_world')->first();

        $this->assertTrue($conversation->unPublishConversation());
        $this->assertEquals(ConversationNode::DEACTIVATED, $conversation->status);

        $this->assertTrue($conversation->archiveConversation());
        $this->assertEquals(ConversationNode::ARCHIVED, $conversation->status);

        /** @var DGraphClient $client */
        $client = app()->make(DGraphClient::class);

        /** @var DGraphQuery $query */
        $query = new DGraphQuery();
        $query->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->filterEq('id', 'hello_bot_world')
            ->setQueryGraph(DGraphConversationQueryFactory::getConversationTemplateQueryGraph());

        /** @var EIModelCreator $eiModelCreator */
        $eiModelCreator = app()->make(EIModelCreator::class);

        $response = $client->query($query);
        $this->assertCount(1, $response->getData());
        $model = $eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);
        $this->assertEquals(ConversationNode::ARCHIVED, $model->getConversationStatus());

    }

    public function testDeleting() {
        $this->publishConversation($this->conversation1());

        // Ensure conversation was persisted to DGraph
        /** @var DGraphQuery $query */
        $query = new DGraphQuery();
        $query->eq(Model::EI_TYPE, Model::CONVERSATION_TEMPLATE)
            ->filterEq('id', 'hello_bot_world')
            ->setQueryGraph(DGraphConversationQueryFactory::getConversationTemplateQueryGraph());

        /** @var DGraphClient $client */
        $client = app()->make(DGraphClient::class);

        $response = $client->query($query);
        $this->assertCount(1, $response->getData());

        // Archive conversation
        /** @var Conversation $conversation */
        $conversation = Conversation::where('name', 'hello_bot_world')->first();

        $conversation->unPublishConversation();
        $conversation->archiveConversation();

        // Delete conversation
        $this->assertTrue($conversation->delete());

        $response = $client->query($query);
        $this->assertCount(0, $response->getData());
        $this->assertTrue(Conversation::where('name', 'hello_bot_world')->get()->isEmpty());
    }
}
