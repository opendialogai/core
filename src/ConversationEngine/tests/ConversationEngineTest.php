<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelConversationConverter;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

class ConversationEngineTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    /* @var WebchatChatOpenUtterance */
    private $utterance;

    public function setUp(): void
    {
        parent::setUp();
        /* @var AttributeResolver $attributeResolver */
        $attributeResolver = $this->app->make(AttributeResolver::class);
        $attributes = ['test' => IntAttribute::class];
        $attributeResolver->registerAttributes($attributes);

        $this->conversationEngine = $this->app->make(ConversationEngineInterface::class);

        $this->initDDgraph();

        for ($i = 1; $i <= 4; $i++) {
            $conversationId = 'conversation' . $i;
            $this->publishConversation($this->$conversationId());
        }

        $this->utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
    }

    public function testConversationStoreIntents()
    {
        $conversationStore = $this->conversationEngine->getConversationStore();
        $openingIntents = $conversationStore->getAllOpeningIntents();

        $this->assertCount(4, $openingIntents);
    }

    public function testConversationConditions()
    {
        /* @var ConversationStoreInterface $conversationStore */
        $conversationStore = $this->conversationEngine->getConversationStore();

        $conversationModel = $conversationStore->getConversationTemplate('hello_bot_world');

        $conversationConverter = app()->make(EIModelConversationConverter::class);
        $conversation = $conversationConverter::buildConversationFromEIModel($conversationModel);

        $conditions = $conversation->getConditions();

        $this->assertCount(2, $conditions);

        /* @var Condition $condition */
        foreach ($conditions as $condition) {
            $attribute = $condition->getAttributeToCompareAgainst();
            $this->assertContains($attribute->getId(), ['name', 'test']);

            if ($condition->getId() === 'user.name-is_set-') {
                $this->assertInstanceOf(StringAttribute::class, $condition->getAttributeToCompareAgainst());
                $this->assertNull($condition->getAttributeToCompareAgainst()->getValue());
                $this->assertEquals('name', $condition->getAttribute(Model::ATTRIBUTE_NAME)->getValue());
                $this->assertNull($condition->getAttribute(Model::ATTRIBUTE_VALUE)->getValue());
                $this->assertEquals(AbstractAttribute::IS_SET, $condition->getEvaluationOperation());
                $this->assertEquals(AbstractAttribute::IS_SET, $condition->getAttribute(Model::OPERATION)->getValue());
            }

            if ($condition->getId() === 'user.test-gt-10') {
                $this->assertInstanceOf(IntAttribute::class, $condition->getAttributeToCompareAgainst());
                $this->assertEquals(10, $condition->getAttributeToCompareAgainst()->getValue());
                $this->assertEquals(10, $condition->getAttribute(Model::ATTRIBUTE_VALUE)->getValue());
                $this->assertEquals('test', $condition->getAttribute(Model::ATTRIBUTE_NAME)->getValue());
                $this->assertEquals(AbstractAttribute::GREATER_THAN, $condition->getEvaluationOperation());
                $this->assertEquals(AbstractAttribute::GREATER_THAN, $condition->getAttribute(Model::OPERATION)->getValue());
            }

        }
    }

    /**
     * @throws FieldNotSupported
     */
    public function testConversationEngineNoOngoingConversation()
    {
        $userContext = $this->createUserContext();
        $this->assertEquals($this->utterance->getUserId(), $userContext->getUserId());
        $this->assertFalse($userContext->isUserHavingConversation());
    }

    /**
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testConversationEngineOngoingConversation()
    {
        /* @var UserContext $userContext; */
        $userContext = $this->createConversationAndAttachToUser();
        $this->assertEquals($this->utterance->getUserId(), $userContext->getUserId());
        $this->assertTrue($userContext->isUserHavingConversation());
    }

    /**
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function testDeterminingCurrentConversationWithoutOngoingConversation()
    {
        $userContext = $this->createUserContext();

        $conversation = $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);
        $this->assertEquals('no_match_conversation', $conversation->getId());
        $this->assertEquals('no_match_conversation', $userContext->getCurrentConversation()->getId());
    }

    /**
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function testDeterminingNextIntentWithoutOngoingConversation()
    {
        // This is setup to match the NoMatch conversation

        $userContext = $this->createUserContext();

        $intent = $this->conversationEngine->getNextIntent($userContext, $this->utterance);
        $this->assertEquals('intent.core.NoMatchResponse', $intent->getId());

        $this->assertFalse($userContext->isUserHavingConversation());
    }

    /**
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function testDeterminingNextIntentsInMultiSceneConversation()
    {
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new IntAttribute('test', 11));

        $this->utterance->setCallbackId('hello_bot');
        /* @var InterpreterServiceInterface $interpreterService */
        $interpreterService = $this->app->make(InterpreterServiceInterface::class);
        /* @var CallbackInterpreter $callbackInterpeter */
        $callbackInterpeter = $interpreterService->getDefaultInterpreter();
        $callbackInterpeter->addCallback('hello_bot', 'hello_bot');
        $callbackInterpeter->addCallback('how_are_you', 'how_are_you');
        $callbackInterpeter->addCallback('hello_registered_user', 'hello_registered_user');

        // Let's see if we get the right next intent for the first step.
        $intent = $this->conversationEngine->getNextIntent($userContext, $this->utterance);
        $validIntents = ['hello_user','hello_registered_user'];
        $this->assertContains($intent->getId(), $validIntents);

        $this->assertContains($userContext->getCurrentIntent()->getIntentId(), $validIntents);

        // Ok, now the conversation has moved on let us take the next step
        /* @var WebchatChatOpenUtterance $nextUtterance */
        $nextUtterance = new WebchatChatOpenUtterance();
        if ($intent->getId() === 'hello_user') {
            $nextUtterance->setCallbackId('how_are_you');
            $intent = $this->conversationEngine->getNextIntent($userContext, $nextUtterance);
            $this->assertEquals('doing_dandy', $intent->getId());
        }
        if ($intent->getId() === 'hello_registered_user') {
            $nextUtterance->setCallbackId('weather_question');
            $intent = $this->conversationEngine->getNextIntent($userContext, $nextUtterance);
            $this->assertEquals('intent.core.NoMatchResponse', $intent->getId());
        }
    }


    /**
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException
     * @throws \OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     */
    public function testDeterminingCurrentConversationWithOngoingConversation()
    {
        $userContext = $this->createConversationAndAttachToUser();

        $conversation = $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);

        // Ensure that the $conversation is the right one.
        $this->assertEquals($conversation->getId(), 'hello_bot_world');
        $this->assertCount(4, $conversation->getAllScenes());
        $this->assertEquals('opening_scene', $conversation->getScene('opening_scene')->getId());
        $this->assertEquals('scene2', $conversation->getScene('scene2')->getId());

        $openingScene = $conversation->getScene('opening_scene');
        $this->assertCount(1, $openingScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $openingScene->getIntentsSaidByUser()->get('hello_bot');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertEquals($userIntent->getAction()->getId(), 'action.core.example');
        $this->assertEquals($userIntent->getInterpreter()->getId(), 'interpreter.core.callbackInterpreter');

        $this->assertCount(2, $openingScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $openingScene->getIntentsSaidByBot()->get('hello_user');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertEquals('action.core.example', $botIntent->getAction()->getId());

        $secondScene = $conversation->getScene('scene2');

        $this->assertCount(1, $secondScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $secondScene->getIntentsSaidByUser()->get('how_are_you');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertEquals('action.core.example', $userIntent->getAction()->getId());
        $this->assertEquals('interpreter.core.callbackInterpreter', $userIntent->getInterpreter()->getId());

        $this->assertCount(1, $secondScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $secondScene->getIntentsSaidByBot()->get('doing_dandy');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertEquals('action.core.example', $botIntent->getAction()->getId());
    }

    private function createUserContext()
    {
        $userContext = ContextService::createUserContext($this->utterance);

        return $userContext;
    }

    /**
     * @return UserContext
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function createConversationAndAttachToUser()
    {
        /* @var Conversation $conversation */
        $conversation = Conversation::create(['name' => 'Conversation1', 'model' => $this->conversation1()]);
        /* @var \OpenDialogAi\Core\Conversation\Conversation $conversationModel */
        $conversationModel = $conversation->buildConversation();

        /* @var UserContext $userContext */
        $userContext = $this->createUserContext();
        $user = $userContext->getUser();

        $user->setCurrentConversation($conversationModel);
        $userContext->updateUser();

        $conversationStore = app()->make(ConversationStoreInterface::class);
        $conversationModel = $conversationStore->getConversation($userContext->getUser()->getCurrentConversationUid());

        $conversationConverter = app()->make(EIModelConversationConverter::class);
        $conversation = $conversationConverter::buildConversationFromEIModel($conversationModel);

        /* @var Scene $scene */
        $scene = $conversation->getOpeningScenes()->first()->value;
        $intent = $scene->getIntentByOrder(1);

        $userContext->setCurrentIntent($intent);
        $userContext->updateUser();

        return $userContext;
    }

}
