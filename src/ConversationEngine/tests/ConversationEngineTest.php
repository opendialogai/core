<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\User;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\OperationEngine\Operations\GreaterThanOperation;
use OpenDialogAi\OperationEngine\Operations\IsSetOperation;

class ConversationEngineTest extends TestCase
{
    /* @var \OpenDialogAi\ConversationEngine\ConversationEngine */
    private $conversationEngine;

    /* @var \OpenDialogAi\Core\Graph\DGraph\DGraphClient */
    private $client;

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

        $this->client = $this->app->make(DGraphClient::class);
        $this->client->dropSchema();
        $this->client->initSchema();

        for ($i = 1; $i <= 4; $i++) {
            $conversationId = 'conversation' . $i;

            // Now create and store three conversations
            $conversation = Conversation::create(['name' => 'Conversation1', 'model' => $this->$conversationId()]);
            $conversationModel = $conversation->buildConversation();

            $this->assertTrue($conversation->publishConversation($conversationModel));
        }

        // Create an utterance
        $user = new User('1');
        $user->setFirstName('John');
        $user->setLastName('Smith');
        $utterance = new WebchatChatOpenUtterance();
        $utterance->setCallbackId('hello_bot');
        $utterance->setUser($user);
        $this->utterance = $utterance;

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

        $conversation = $conversationStore->getConversationTemplate('hello_bot_world');
        $conditions = $conversation->getConditions();

        $this->assertCount(2, $conditions);

        /* @var \OpenDialogAi\Core\Conversation\Condition $condition */
        foreach ($conditions as $condition) {
            if ($condition->getId() == 'user.name-is_set-') {
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_NAME)->getValue() === 'name');
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_VALUE)->getValue() === null);
                $this->assertTrue($condition->getEvaluationOperation() == IsSetOperation::NAME);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == IsSetOperation::NAME);
            }

            if ($condition->getId() == 'user.test-gt-10') {
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_VALUE)->getValue() === 10);
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_NAME)->getValue() === 'test');
                $this->assertTrue($condition->getEvaluationOperation() == GreaterThanOperation::NAME);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == GreaterThanOperation::NAME);
            }

        }
    }

    public function testConversationEngineNoOngoingConversation()
    {
        /* @var \OpenDialogAi\ContextEngine\Contexts\UserContext $userContext; */
        $userContext = $this->createUserContext('abc123a');
        $this->assertTrue($userContext->getUserId() == 'abc123a');
        $this->assertFalse($userContext->isUserHavingConversation());
    }

    public function testConversationEngineOngoingConversation()
    {
        /* @var \OpenDialogAi\ContextEngine\Contexts\UserContext $userContext; */
        $userContext = $this->createConversationAndAttachToUser('abc123b');
        $this->assertTrue($userContext->getUserId() == 'abc123b');
        $this->assertTrue($userContext->isUserHavingConversation());
    }

    public function testDeterminingCurrentConversationWithoutOngoingConversation()
    {
        $userContext = $this->createUserContext('abc123a');

        $conversation = $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);
        $this->assertTrue($conversation->getId() == 'no_match_conversation');
        $this->assertTrue($userContext->getCurrentConversation()->getId() == 'no_match_conversation');
    }

    public function testDeterminingNextIntentWithoutOngoingConversation()
    {
        // This is setup to match the NoMatch conversation

        $userContext = $this->createUserContext('abc123a');

        $intent = $this->conversationEngine->getNextIntent($userContext, $this->utterance);
        $this->assertTrue($intent->getId() == 'intent.core.NoMatchResponse');
        $this->assertFalse($userContext->isUserHavingConversation());
    }

    public function testDeterminingNextIntentsInMultiSceneConversation()
    {
        $userContext = $this->createUserContext('abc123a');
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
        $this->assertTrue(in_array($intent->getId(), $validIntents));

        $this->assertTrue(in_array($userContext->getCurrentIntent()->getId(), $validIntents));

        // Ok, now the conversation has moved on let us take the next step
        /* @var WebchatChatOpenUtterance $nextUtterance */
        $nextUtterance = new WebchatChatOpenUtterance();
        if ($intent->getId() == 'hello_user') {
            $nextUtterance->setCallbackId('how_are_you');
            $intent = $this->conversationEngine->getNextIntent($userContext, $nextUtterance);
            $this->assertTrue($intent->getId()=='doing_dandy');
        }
        if ($intent->getId() == 'hello_registered_user') {
            $nextUtterance->setCallbackId('weather_question');
            $intent = $this->conversationEngine->getNextIntent($userContext, $nextUtterance);
            $this->assertTrue($intent->getId() =='intent.core.NoMatchResponse');
        }
    }


    public function testDeterminingCurrentConversationWithOngoingConversation()
    {
        $userContext = $this->createConversationAndAttachToUser('abc123a');

        $conversation = $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);

        // Ensure that the $conversation is the right one.
        $this->assertTrue($conversation->getId() == 'hello_bot_world');
        $this->assertCount(3, $conversation->getAllScenes());
        $this->assertTrue($conversation->getScene('opening_scene')->getId() == 'opening_scene');
        $this->assertTrue($conversation->getScene('scene2')->getId() == 'scene2');

        $openingScene = $conversation->getScene('opening_scene');
        $this->assertCount(1, $openingScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $openingScene->getIntentsSaidByUser()->get('hello_bot');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertTrue($userIntent->getAction()->getId() == 'action.core.example');
        $this->assertTrue($userIntent->getInterpreter()->getId() == 'interpreter.core.callbackInterpreter');

        $this->assertCount(2, $openingScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $openingScene->getIntentsSaidByBot()->get('hello_user');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertTrue($botIntent->getAction()->getId() == 'action.core.example');

        $secondScene = $conversation->getScene('scene2');

        $this->assertCount(1, $secondScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $secondScene->getIntentsSaidByUser()->get('how_are_you');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertTrue($userIntent->getAction()->getId() == 'action.core.example');
        $this->assertTrue($userIntent->getInterpreter()->getId() == 'interpreter.core.callbackInterpreter');

        $this->assertCount(1, $secondScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $secondScene->getIntentsSaidByBot()->get('doing_dandy');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertTrue($botIntent->getAction()->getId() == 'action.core.example');
    }

    /**
     * @param $userId
     * @return \OpenDialogAi\ContextEngine\Contexts\UserContext
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     */
    private function createUserContext($userId)
    {
        $this->utterance->setUserId($userId);

        /* @var \OpenDialogAi\ContextEngine\ContextManager\ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);

        /* @var \OpenDialogAi\ContextEngine\Contexts\UserContext $userContext; */
        $userContext = $contextService->createUserContext($this->utterance);

        return $userContext;
    }

    /**
     * @param $userId
     * @return \OpenDialogAi\ContextEngine\Contexts\UserContext
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     */
    private function createConversationAndAttachToUser($userId)
    {
        $this->utterance->setUserId($userId);

        /* @var \OpenDialogAi\ConversationBuilder\Conversation $conversation */
        $conversation = Conversation::create(['name' => 'Conversation1', 'model' => $this->conversation1()]);
        /* @var \OpenDialogAi\Core\Conversation\Conversation $conversationModel */
        $conversationModel = $conversation->buildConversation();

        /* @var \OpenDialogAi\ContextEngine\Contexts\UserContext $userContext */
        $userContext = $this->createUserContext($userId);
        $user = $userContext->getUser();

        $user->setCurrentConversation($conversationModel);
        /* @var Scene $scene */
        $scene = $user->getCurrentConversation()->getOpeningScenes()->first()->value;
        $intent = $scene->getIntentByOrder(1);
        $user->setCurrentIntent($intent);
        $userContext->updateUser();

        return $userContext;
    }

}
