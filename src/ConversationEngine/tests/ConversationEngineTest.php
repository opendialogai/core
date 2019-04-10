<?php


namespace OpenDialogAi\ConversationEngine\tests;


use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;

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

        for ($i = 1; $i <= 3; $i++) {
            $conversationId = 'conversation' . $i;

            // Now create and store three conversations
            $conversation = Conversation::create(['name' => 'Conversation1', 'model' => $this->$conversationId()]);
            $conversationModel = $conversation->buildConversation();

            $this->assertTrue($conversation->publishConversation($conversationModel));
        }

        // Create an utterance
        $utterance = new WebchatChatOpenUtterance();
        $utterance->setCallbackId('chat_open');
        $this->utterance = $utterance;
    }

    public function testConversationStoreIntents()
    {
        $conversationStore = $this->conversationEngine->getConversationStore();
        $openingIntents = $conversationStore->getAllOpeningIntents();
        //dd($openingIntents);

        $this->assertCount(3, $openingIntents);
        $validInterpreters = ['hello_interpreter1', 'hello_interpreter2'];
        $validIntent = 'hello_bot';
        foreach ($openingIntents as $uid => $intent) {
            if (is_array($intent)) {
                $this->assertTrue($intent[Model::INTENT] == $validIntent);
                $this->assertTrue(in_array($intent[Model::INTENT_INTERPRETER], $validInterpreters));
            } else {
                $this->assertTrue($intent == 'hello_bot');
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

        dd($this->conversationEngine->determineCurrentConversation($userContext, $this->utterance));
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
        $this->assertTrue($userIntent->getInterpreter()->getId() == 'hello_interpreter1');

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
        $this->assertTrue($userIntent->getInterpreter()->getId() == 'how_are_you_interpreter');

        $this->assertCount(1, $secondScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $secondScene->getIntentsSaidByBot()->get('doing_dandy');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertTrue($botIntent->getAction()->getId() == 'wave_back');

        // Change the relationships between the user and this conversation
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
        $userContext->updateUser();

        return $userContext;
    }
}