<?php


namespace OpenDialogAi\ConversationEngine\tests;


use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\OpeningIntent;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;

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
        $utterance = new WebchatChatOpenUtterance();
        $utterance->setCallbackId('hello_bot');
        $this->utterance = $utterance;
    }

    public function testConversationStoreIntents()
    {
        $conversationStore = $this->conversationEngine->getConversationStore();
        $openingIntents = $conversationStore->getAllOpeningIntents();

        $this->assertCount(4, $openingIntents);
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
    }

    public function testDeterminingCurrentConversationWithOngoingConversation()
    {
        $userContext = $this->createConversationAndAttachToUser('abc123a');

        $conversation = $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);

        // Ensure that the $conversation is the right one.
        $this->assertTrue($conversation->getId() == 'hello_bot_world');
        $this->assertCount(2, $conversation->getAllScenes());
        $this->assertTrue($conversation->getScene('opening_scene')->getId() == 'opening_scene');
        $this->assertTrue($conversation->getScene('scene2')->getId() == 'scene2');

        $openingScene = $conversation->getScene('opening_scene');
        $this->assertCount(1, $openingScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $openingScene->getIntentsSaidByUser()->get('hello_bot');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertTrue($userIntent->getAction()->getId() == 'register_hello');
        $this->assertTrue($userIntent->getInterpreter()->getId() == 'interpreter.core.callbackInterpreter');

        $this->assertCount(1, $openingScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $openingScene->getIntentsSaidByBot()->get('hello_user');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertTrue($botIntent->getAction()->getId() == 'register_hello');

        $secondScene = $conversation->getScene('scene2');

        $this->assertCount(1, $secondScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $secondScene->getIntentsSaidByUser()->get('how_are_you');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertTrue($userIntent->getAction()->getId() == 'wave');
        $this->assertTrue($userIntent->getInterpreter()->getId() == 'interpreter.core.callbackInterpreter');

        $this->assertCount(1, $secondScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $secondScene->getIntentsSaidByBot()->get('doing_dandy');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertTrue($botIntent->getAction()->getId() == 'wave_back');
    }

    public function testNextPossibleIntents()
    {
        $userContext = $this->createConversationAndAttachToUser('abc123a');

        $this->utterance->setCallbackId('hello_bot');
        /* @var InterpreterServiceInterface $interpreterService */
        $interpreterService = $this->app->make(InterpreterServiceInterface::class);
        /* @var CallbackInterpreter $callbackInterpeter */
        $defaultInterpreter = $interpreterService->getDefaultInterpreter();
        $defaultInterpreter->addCallback('hello_bot', 'hello_bot');

        $this->conversationEngine->getNextIntent($userContext, $this->utterance);

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