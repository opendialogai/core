<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\RequestLog;
use OpenDialogAi\Core\ResponseLog;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

class CoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testEndToEndFunctionality() {

        // Make sure we have a ResponseEngine
        $service = $this->app->make(ResponseEngineService::class);
        $service->registerAvailableFormatters();
        $formatters = $service->getAvailableFormatters();
        $this->assertCount(1, $formatters);
        $this->assertContains('formatter.core.webchat', array_keys($formatters));

        // Create a message and assign it to an intent
        // Ensure that we can create an intent.
        OutgoingIntent::create(['name' => 'intent.core.NoMatchResponse']);
        $intent = OutgoingIntent::where('name', 'intent.core.NoMatchResponse')->first();
        $this->assertEquals('intent.core.NoMatchResponse', $intent->name);

        $markUp = (new MessageMarkUpGenerator())->addTextMessage('Friendly Reply.');
        $messageTemplate = MessageTemplate::create(
            [
                'name' => 'Friendly Reply',
                'outgoing_intent_id' => $intent->id,
                'message_markup' => $markUp->getMarkUp(),
            ]
        );

        $this->assertEquals('Friendly Reply', MessageTemplate::where('name', 'Friendly Reply')->first()->name);

        // Ensure we can get a MessageTemplate's OutgoingIntent.
        $this->assertEquals($intent->id, $messageTemplate->outgoingIntent->id);

        // Ensure we can get a OutgoingIntent's MessageTemplates.
        $this->assertTrue($intent->messageTemplates->contains($messageTemplate));

        // Mock selectors
        $scenario = new Scenario();
        $scenario->setODId('test_scenario1');

        ScenarioSelector::shouldReceive('selectScenarioById')
            ->once()
            ->andReturn($scenario);

        $conversation = new Conversation($scenario);
        $conversation->setODId('no_match_conversation');
        ConversationSelector::shouldReceive('selectConversationById')
            ->once()
            ->andReturn($conversation);

        $scene = new Scene($conversation);
        $scene->setODId('starting_scene');
        SceneSelector::shouldReceive('selectSceneById')
            ->once()
            ->andReturn($scene);

        $turn = new Turn($scene);
        $turn->setODId('starting_turn');
        TurnSelector::shouldReceive('selectTurnById')
            ->once()
            ->andReturn($turn);

        $noMatchResponseIntent = new Intent($turn, Intent::APP);
        $noMatchResponseIntent->setIsRequestIntent(false);
        $noMatchResponseIntent->setODId('intent.core.NoMatchResponse');
        $noMatchResponseIntent->setConfidence(1);

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->andReturn(new IntentCollection([$noMatchResponseIntent]));

        // Send a message to /incoming/webchat
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'text',
                'data' => [
                    'text' => 'test message'
                ],
                'user' => [
                    'ipAddress' => '127.0.0.1',
                    'country' => 'UK',
                    'browserLanguage' => 'en-gb',
                    'os' => 'macos',
                    'browser' => 'safari',
                    'timezone' => 'GMT',
                ],
            ],
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(['data' => ['text' => 'Friendly Reply.']]);
    }


    /**
     * @requires DGRAPH
     * @group skip
     * Test that requests to the incoming endpoint are logged.
     */
    public function testApiLogging()
    {
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'text',
                'data' => [
                    'text' => 'test message'
                ],
                'user' => [
                    'ipAddress' => '127.0.0.1',
                    'country' => 'UK',
                    'browserLanguage' => 'en-gb',
                    'os' => 'macos',
                    'browser' => 'safari',
                    'timezone' => 'GMT',
                ],
            ],
        ]);
        $response
            ->assertStatus(200)
            ->assertJson(['data' => ['text' => 'No messages found for intent intent.core.NoMatchResponse']]);

        // Ensure that the request was logged.
        $this->assertDatabaseHas('request_logs', [
            'method' => 'POST',
            'source_ip' => '127.0.0.1',
        ]);
        $this->assertEquals(1, RequestLog::all()->count());

        // Ensure that responses are logged.
        $this->assertDatabaseHas('response_logs', [
            'http_status' => 200
        ]);
        $this->assertEquals(1, ResponseLog::all()->count());

        // Check that requests/responses are not logged for non-incoming endpoints.
        $response = $this->json('GET', '/config');
        $response->assertStatus(200);
        $this->assertEquals(1, RequestLog::all()->count());
        $this->assertEquals(1, ResponseLog::all()->count());
    }
}
