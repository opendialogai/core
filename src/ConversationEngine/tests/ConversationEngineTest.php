<?php


namespace OpenDialogAi\ConversationEngine\tests;


use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
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

    public function testConversationEngineCurrentConversation()
    {
        $this->createConversationAndAttachToUser();

        // Now
    }


    private function createUserContext()
    {
        // Create an utterance
        $utterance = new WebchatChatOpenUtterance();
        $utterance->setCallbackId('chat_open');
        $utterance->setUserId('abcdefg123');

        /* @var \OpenDialogAi\ContextEngine\ContextManager\ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);

        /* @var \OpenDialogAi\ContextEngine\Contexts\UserContext $userContext; */
        $userContext = $contextService->createUserContext($utterance);

        return $userContext;
    }

    private function createConversationAndAttachToUser()
    {
        /* @var \OpenDialogAi\ConversationBuilder\Conversation $conversation */
        $conversation = Conversation::create(['name' => 'Conversation1', 'model' => $this->conversation1()]);
        /* @var \OpenDialogAi\Core\Conversation\Conversation $conversationModel */
        $conversationModel = $conversation->buildConversation();

        /* @var \OpenDialogAi\ContextEngine\Contexts\UserContext $userContext */
        $userContext = $this->createUserContext();
        $user = $userContext->getUser();

        $user->setCurrentConversation($conversationModel);
        $userContext->updateUser();
    }
}