<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\TimestampAttribute;
use OpenDialogAi\Core\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\Core\ResponseEngine\tests\Formatters\DummyFormatter;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\ConditionsYamlGenerator;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\Exceptions\FormatterNotRegisteredException;
use OpenDialogAi\ResponseEngine\Message\Webchat\ImageMessage;
use OpenDialogAi\ResponseEngine\Message\Message;
use OpenDialogAi\ResponseEngine\Message\WebChatMessageFormatter;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\NoMatchingMessagesException;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use OpenDialogAi\ResponseEngine\Rules\MessageConditions;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;

class ResponseEngineTest extends TestCase
{
    public function testService()
    {
        $formatters = $this->app->make(ResponseEngineService::class)->getAvailableFormatters();
        $this->assertCount(1, $formatters);
        $this->assertContains('formatter.core.webchat', array_keys($formatters));
    }

    public function testUnknownService()
    {
        $sensorService = $this->app->make(ResponseEngineService::class);
        $this->expectException(FormatterNotRegisteredException::class);
        $sensorService->getFormatter('formatter.core.unknown');
    }

    public function testWebchatFormatter()
    {
        $webchatFormatter = new WebChatMessageFormatter();
        $this->assertEquals('formatter.core.webchat', $webchatFormatter->getName());
    }

    public function testBadlyNamedSensor()
    {
        $this->app['config']->set(
            'opendialog.response_engine.available_filters',
            [DummyFormatter::class]
        );

        $formatterService = $this->app->make(ResponseEngineService::class);

        $this->assertCount(1, $formatterService->getAvailableFormatters());
    }

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

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        /** @var TimestampAttribute $timestamp */
        $timestamp = $messageTemplate->getConditions()['user'][0]['timestamp']->getAttribute('timestamp');
        $this->assertSame($timestamp->getId(), 'timestamp');
        $this->assertSame($timestamp->getValue(), 10000);

        $timestamp = $messageTemplate->getConditions()['user'][1]['timestamp']->getAttribute('timestamp');
        $this->assertSame($timestamp->getId(), 'timestamp');
        $this->assertSame($timestamp->getValue(), 20000);

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
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
        $this->assertInstanceOf(
            OpenDialogMessages::class,
            $messageWrapper
        );

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
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Message', $messageWrapper->getMessages()[0]);
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
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\ImageMessage', $messageWrapper->getMessages()[0]);
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
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\ButtonMessage', $messageWrapper->getMessages()[0]);
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
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');

        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\ButtonMessage', $messageWrapper->getMessages()[0]);
    }

    public function testWebChatAttributeMessage()
    {
        /* @var ContextService $contextService */
        $userContext = $this->createUserContext();
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
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');

        $this->assertInstanceOf(Message::class, $messageWrapper->getMessages()[0]);
        $this->assertInstanceOf(ImageMessage::class, $messageWrapper->getMessages()[1]);
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
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));
        $userContext->addAttribute(new StringAttribute('message', $generator->getMarkUp()));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');

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
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
        $this->assertInstanceOf(
            OpenDialogMessages::class,
            $messageWrapper
        );

        $this->assertEquals(
            $messageWrapper->getMessages()[0]->getText(),
            'This is an example <a target="_blank" href="http://www.example.com">This is a link</a>'
        );
    }

    public function testAllAttributesMayBeNull()
    {
        $attribute = new BooleanAttribute('name', null);
        $this->assertSame(null, $attribute->getValue());

        $attribute = new FloatAttribute('name', null);
        $this->assertSame(null, $attribute->getValue());

        $attribute = new IntAttribute('name', null);
        $this->assertSame(null, $attribute->getValue());

        $attribute = new StringAttribute('name', null);
        $this->assertSame(null, $attribute->getValue());

        $attribute = new TimestampAttribute('name', null);
        $this->assertSame(null, $attribute->getValue());
    }

    public function testTimeStampAttributeNotPresent()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', 'dummy', 'eq');
        $conditions->addCondition('user.last_seen', null, 'is_set');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
    }

    public function testStringAttributeNotPresent()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.name', null, 'is_set');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
    }

    public function testGreaterThanOperator()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there!");

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.last_seen', 600, 'time_passed_greater_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new TimestampAttribute('last_seen', now()->timestamp - 700));
        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'Hi there!');
    }

    public function testGreaterThanOperatorWithNoAttribute()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.last_seen', 600, 'time_passed_greater_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
    }

    public function testLessThanOperator()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there!");

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.last_seen', 600, 'time_passed_less_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();;

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new TimestampAttribute('last_seen', now()->timestamp - 300));
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'Hi there!');
    }

    public function testLessThanOperatorWithNoAttribute()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition('user.last_seen', 600, 'time_passed_less_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('formatter.core.webchat','Hello');
    }

    /**
     * @return ContextInterface
     */
    public function createUserContext(): ContextInterface
    {
        $userContext = ContextService::createContext('user');
        return $userContext;
    }
}
