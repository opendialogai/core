<?php

namespace OpenDialogAi\ResponseEngine\Tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\FloatAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\TimestampAttribute;
use OpenDialogAi\Core\ResponseEngine\tests\Formatters\DummyFormatter;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\ConditionsYamlGenerator;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\OperationEngine\Operations\GreaterThanOrEqualOperation;
use OpenDialogAi\OperationEngine\Operations\LessThanOrEqualOperation;
use OpenDialogAi\ResponseEngine\Exceptions\FormatterNotRegisteredException;
use OpenDialogAi\ResponseEngine\Formatters\Webchat\WebChatMessageFormatter;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatImageMessage;
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
        $service = $this->app->make(ResponseEngineService::class);
        $service->registerAvailableFormatters();
        $formatters = $service->getAvailableFormatters();
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

        $attributes = ['usertimestamp' => 'user.timestamp'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, ['value' => 10000], 'gte');
        $conditions->addCondition($attributes, ['value' => 20000], 'lte');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => 'Hi there!',
        ]);

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        $condition1 = $messageTemplate->getConditions()[0];
        $condition2 = $messageTemplate->getConditions()[1];

        $this->assertequals($condition1->getEvaluationOperation(), GreaterThanOrEqualOperation::$name);
        $this->assertequals($condition2->getEvaluationOperation(), LessThanOrEqualOperation::$name);
        $this->assertequals($condition1->getParameters()['value'], 10000);
        $this->assertequals($condition2->getParameters()['value'], 20000);

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

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

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
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');
        $this->assertInstanceOf(
            OpenDialogMessages::class,
            $messageWrapper
        );

        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'Hi there dummy!');
    }

    public function testResponseEngineServiceWithArrayAttribute()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("{user.phraseArray}");

        $conditions = new ConditionsYamlGenerator();

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new ArrayAttribute('phraseArray', [
            'greeting' => 'hello',
            'subject' => 'world'
        ]));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');
        $this->assertInstanceOf(
            OpenDialogMessages::class,
            $messageWrapper
        );

        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), '{&quot;greeting&quot;:&quot;hello&quot;,&quot;subject&quot;:&quot;world&quot;}');
    }

    public function testWebChatMessage()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $generator->addTextMessage('hi there');

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

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
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');

        // phpcs:ignore
        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\OpenDialogMessage', $messageWrapper->getMessages()[0]);
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

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

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
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');

        // phpcs:ignore
        $this->assertInstanceOf(
            WebchatImageMessage::class,
            $messageWrapper->getMessages()[0]
        );
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

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

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
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');

        // phpcs:ignore
        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebchatButtonMessage', $messageWrapper->getMessages()[0]);
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

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

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
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');

        // phpcs:ignore
        $this->assertInstanceOf('OpenDialogAi\ResponseEngine\Message\Webchat\WebchatButtonMessage', $messageWrapper->getMessages()[0]);
    }

    public function testWebChatButtonMessageWithExternalButtons()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $generator = new MessageMarkUpGenerator();
        $buttons = [
            [
                'text' => 'Button 1',
                'callback' => 'callback1',
                'value' => 'value'
            ],
            [
                'text' => 'Button 2',
                'callback' => 'callback2',
                'value' => 'value'
            ],
            [
                'text' => 'Button 3',
                'callback' => 'callback3',
                'value' => 'value'
            ]
        ];
        $generator->addButtonMessage('test button', $buttons, true);

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

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
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');

        $message = $messageWrapper->getMessages()[0];

        $this->assertInstanceOf(WebchatButtonMessage::class, $message);

        $this->assertEquals($message->getData()['external'], true);
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

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $generator2->getMarkUp(),
        ]);

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');

        $this->assertInstanceOf(OpenDialogMessage::class, $messageWrapper->getMessages()[0]);
        $this->assertInstanceOf(WebchatImageMessage::class, $messageWrapper->getMessages()[1]);
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

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

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
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');

        $this->assertEquals('hi dummy there   welcome', $messageWrapper->getMessages()[0]->getText());
    }

    public function testMessageConditionRules()
    {
        $conditionsValidator = new MessageConditions();

        // Test valid condition.
        $conditions = "---\nconditions:\n- condition:\n    attributes:\n      username: user.name\n    parameters:\n      value: dummy\n    operation: eq";
        $this->assertTrue($conditionsValidator->passes(null, $conditions));

        // Test invalid condition.
        $conditions = "---\nconditions:\n-\n    attributes:\n      username: user.name\n    parameters:\n      value: dummy\n    operation: eq";
        $this->assertFalse($conditionsValidator->passes(null, $conditions));

        // Test condition without enough attributes.
        $conditions = "---\nconditions:\n-\n    operation: eq";
        $this->assertFalse($conditionsValidator->passes(null, $conditions));

        // Test condition without operation.
        $conditions = "---\nconditions:\n-\n    attributes:\n      username: user.name\n    parameters:\n      value: dummy";
        $this->assertFalse($conditionsValidator->passes(null, $conditions));
    }

    public function testWebChatTextMessageWithLink()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        // phpcs:ignore
        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessageWithLink('This is an example', 'This is a link', 'http://www.example.com');

        $attributes = ['username' => 'user.name'];
        $parameters = ['value' => 'dummy'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'eq');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');
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
        $conditions->addCondition(['username' => 'user.name'], ['value' => 'dummy'], 'eq');
        $conditions->addCondition(['userlastseen' => 'user.last_seen'], [], 'is_set');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new StringAttribute('name', 'dummy'));

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $responseEngineService->getMessageForIntent('webchat', 'Hello');
    }

    public function testStringAttributeNotPresent()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        $attributes = ['username' => 'user.name'];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, [], 'is_set');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $this->createUserContext();

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $responseEngineService->getMessageForIntent('webchat', 'Hello');
    }

    public function testGreaterThanOperator()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there!");

        $attributes = ['userlastseen' => 'user.last_seen'];
        $parameters = ['value' => 600];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'time_passed_greater_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new TimestampAttribute('last_seen', now()->timestamp - 700));
        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');
        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'Hi there!');
    }

    public function testGreaterThanOperatorWithNoAttribute()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        $attributes = ['userlastseen' => 'user.last_seen'];
        $parameters = ['value' => 600];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'time_passed_greater_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $this->createUserContext();

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $responseEngineService->getMessageForIntent('webchat', 'Hello');
    }

    public function testLessThanOperator()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there!");

        $attributes = ['userlastseen' => 'user.last_seen'];
        $parameters = ['value' => 600];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'time_passed_less_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $this->createUserContext();
        ;

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new TimestampAttribute('last_seen', now()->timestamp - 300));
        $messageWrapper = $responseEngineService->getMessageForIntent('webchat', 'Hello');
        $this->assertEquals($messageWrapper->getMessages()[0]->getText(), 'Hi there!');
    }

    public function testLessThanOperatorWithNoAttribute()
    {
        OutgoingIntent::create(['name' => 'Hello']);
        $intent = OutgoingIntent::where('name', 'Hello')->first();

        $messageMarkUp = (new MessageMarkUpGenerator())->addTextMessage("Hi there {user.name}!");

        $attributes = ['userlastseen' => 'user.last_seen'];
        $parameters = ['value' => 600];

        $conditions = new ConditionsYamlGenerator();
        $conditions->addCondition($attributes, $parameters, 'time_passed_less_than');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => $conditions->getYaml(),
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        MessageTemplate::where('name', 'Friendly Hello')->first();

        // Setup a context to have something to compare against
        $this->createUserContext();

        $responseEngineService = $this->app->make(ResponseEngineServiceInterface::class);
        $this->expectException(NoMatchingMessagesException::class);
        $responseEngineService->getMessageForIntent('webchat', 'Hello');
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
