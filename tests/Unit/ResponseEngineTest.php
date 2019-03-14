<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ResponseEngine\Facades\ActionEngine;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\Core\Tests\TestCase;

class ResponseEngineTest extends TestCase
{
    public function testResponseDb()
    {
        // Ensure that we can create an intent.
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();
        $this->assertEquals('Hello', $intent->name);

        // Ensure that we can create an message template.
        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => '',
            'message_markup' => 'Hi there!',
        ]);
        $this->assertEquals('Friendly Hello', MessageTemplate::where('name', 'Friendly Hello')->first()->name);
    }

    public function testResponseDbRelationships()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => '',
            'message_markup' => 'Hi there!',
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Ensure we can get a MessageTemplate's OutgoingIntent.
        $this->assertEquals($intent->id, $messageTemplate->outgoingIntent->id);

        // Ensure we can get a OutgoingIntent's MessageTemplates.
        $this->assertTrue($intent->messageTemplates->contains($messageTemplate));
    }

    public function testResponseDbConditionsGetter()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => "---\nconditions:\n-\n  last_message_posted_time: 10000\n  operation: ge\n-\n  last_message_posted_time: 20000\n  operation: le",
            'message_markup' => 'Hi there!',
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        $this->assertEquals($messageTemplate->getConditions(), [
          ['last_message_posted_time' => 10000, 'operation' => 'ge'],
          ['last_message_posted_time' => 20000, 'operation' => 'le'],
        ]);

        MessageTemplate::create([
            'name' => 'Unfriendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => '',
            'message_markup' => 'Whaddya want?!?',
        ]);
        $messageTemplate2 = MessageTemplate::where('name', 'Unfriendly Hello')->first();

        $this->assertEquals($messageTemplate2->getConditions(), []);
    }

    public function testResponseEngineService()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => "---\nconditions:\n-\n  attributes.core.userName: dummy\n  operation: eq",
            'message_markup' => '<message><text-message>Hi there {attributes.core.userName}!</text-message></message>',
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        $responseEngineService = $this->app->make('response-engine-service');
        $message = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage', $message[0]);
    }
}
