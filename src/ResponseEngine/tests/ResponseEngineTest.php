<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;
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
          ['attributeName' => 'last_message_posted_time', 'attributeValue' => 10000, 'operation' => 'ge'],
          ['attributeName' => 'last_message_posted_time', 'attributeValue' => 20000, 'operation' => 'le'],
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

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => "---\nconditions:\n-\n  user.name: dummy\n  operation: eq",
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::CONTEXT_SERVICE);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('user.name', 'dummy'));

        $responseEngineService = $this->app->make('response-engine-service');
        $message = $responseEngineService->getMessageForIntent('Hello');
        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage', $message[0]);
        $this->assertEquals($message[0]->getText(), 'Hi there dummy!');
    }

    public function testWebChatMessage()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $generator->addTextMessage('hi there');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => "---\nconditions:\n-\n  user.name: dummy\n  operation: eq",
            'message_markup' => $generator->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::CONTEXT_SERVICE);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('user.name', 'dummy'));

        $responseEngineService = $this->app->make('response-engine-service');
        $message = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage', $message[0]);
    }

    public function testWebChatImageMessage()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $generator->addImageMessage(
            'https://media1.giphy.com/media/3oKIPuvcQ6CcIy716w/source.gif',
            'http://www.opendialog.ai'
        );

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => "---\nconditions:\n-\n  user.name: dummy\n  operation: eq",
            'message_markup' => $generator->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::CONTEXT_SERVICE);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('user.name', 'dummy'));

        $responseEngineService = $this->app->make('response-engine-service');
        $message = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage', $message[0]);
    }

    public function testWebChatButtonMessage()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $buttons = [
            [
                'text' => 'Button Text',
                'value' => 'Value',
                'callback' => 'callback'
            ]
        ];
        $generator->addButtonMessage('test button', $buttons);

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => "---\nconditions:\n-\n  user.name: dummy\n  operation: eq",
            'message_markup' => $generator->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::CONTEXT_SERVICE);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('user.name', 'dummy'));

        $responseEngineService = $this->app->make('response-engine-service');
        $message = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage', $message[0]);
    }
}
