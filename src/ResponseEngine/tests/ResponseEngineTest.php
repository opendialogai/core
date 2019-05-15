<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\ConditionsYamlGenerator;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use OpenDialogAi\ResponseEngine\Rules\MessageConditions;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

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

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.timestamp', 10000, 'ge');
        $conditions->addCondition('user.timestamp', 20000, 'le');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => 'Hi there!',
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        $this->assertequals($messageTemplate->getConditions()['user'][0]['timestamp']->getAttribute('timestamp')->getId(), 'timestamp');
        $this->assertequals($messageTemplate->getConditions()['user'][0]['timestamp']->getAttribute('timestamp')->getValue(), 10000);
        $this->assertequals($messageTemplate->getConditions()['user'][1]['timestamp']->getAttribute('timestamp')->getId(), 'timestamp');
        $this->assertequals($messageTemplate->getConditions()['user'][1]['timestamp']->getAttribute('timestamp')->getValue(), 20000);

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

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');
        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages', $messageWrapper);
        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'Hi there dummy!');
    }

    public function testWebChatMessage()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $generator->addTextMessage('hi there');

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $generator->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage', $messageWrapper->getMessages()[0]);
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

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $generator->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage', $messageWrapper->getMessages()[0]);
    }

    public function testWebChatButtonMessageWithCallbackButton()
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

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $generator->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage', $messageWrapper->getMessages()[0]);
    }

    public function testWebChatButtonMessageWithTabSwitchButton()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $buttons = [
            [
                'text' => 'Button Text',
                'tab_switch' => true
            ]
        ];
        $generator->addButtonMessage('test button', $buttons);

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $generator->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage', $messageWrapper->getMessages()[0]);
    }

    public function testWebChatAttributeMessage()
    {
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $generator->addTextMessage('hi there');
        $generator->addImageMessage(
            'https://media1.giphy.com/media/3oKIPuvcQ6CcIy716w/source.gif',
            'http://www.opendialog.ai'
        );

        $userContext->addAttribute(new StringAttribute('message', $generator->getMarkUp()));
        $generator2 = (new MessageMarkUpGenerator())
            ->addAttributeMessage('user.message');

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $generator2->getMarkUp(),
        ]);

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');

        $this->assertInstanceOf(WebChatMessage::class, $messageWrapper->getMessages()[0]);
        $this->assertInstanceOf(WebChatImageMessage::class, $messageWrapper->getMessages()[1]);
    }

    public function testWebChatMissingAttributeMessage()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $generator->addTextMessage('hi {user.name} there {missingattribute} welcome');
        $generator->addImageMessage(
            'https://media1.giphy.com/media/3oKIPuvcQ6CcIy716w/source.gif',
            'http://www.opendialog.ai'
        );

        $generator2 = (new MessageMarkUpGenerator())->addAttributeMessage('user.message');

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $generator2->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));
        $userContext->addAttribute(new StringAttribute('message', $generator->getMarkUp()));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');

        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'hi dummy there   welcome');
    }

    public function testMessageConditionRules()
    {
        $conditionsValidator = new MessageConditions();

        // Test valid condition.
        $conditions = "---\nconditions:\n- condition:\n    attribute: user.name\n    value: dummy\n    operation: eq";
        $this->assertTrue($conditionsValidator->passes(null, $conditions));

        // Test invalid condition.
        $conditions = "---\nconditions:\n-\n    attribute: user.name\n    value: dummy\n    operation: eq";
        $this->assertFalse($conditionsValidator->passes(null, $conditions));

        // Test condition without enough attributes.
        $conditions = "---\nconditions:\n-\n    operation: eq";
        $this->assertFalse($conditionsValidator->passes(null, $conditions));

        // Test condition without operation.
        $conditions = "---\nconditions:\n-\n    attribute: user.name\n    value: dummy";
        $this->assertFalse($conditionsValidator->passes(null, $conditions));
    }

    public function testWebChatTextMessageWithLink()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessageWithLink('This is an example', 'This is a link', 'http://www.example.com');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => "---\nconditions:\n- condition:\n    attribute: user.name\n    value: dummy\n    operation: eq",
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        /* @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);
        $userContext = $contextService->createContext('user');
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('Hello');
        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages', $messageWrapper);
        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'This is an example <a target="_blank" href="http://www.example.com">This is a link</a>');
    }
}
