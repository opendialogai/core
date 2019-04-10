<?php

namespace OpenDialogAi\ContextManager\Tests;

use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\ConversationQueryFactory;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;

class UserServiceTest extends TestCase
{
    /* @var UserService */
    private $userService;

    /* @var \OpenDialogAi\Core\Graph\DGraph\DGraphClient */
    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->userService = $this->app->make(UserService::class);
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

    }


    public function testUserCreation()
    {
        $userId = 'newUser' . time();

        $utterance = new WebchatTextUtterance();
        $utterance->setUserId($userId);

        $this->assertTrue(!$this->userService->userExists($userId));

        $this->userService->createOrUpdateUser($utterance);

        $this->assertTrue($this->userService->userExists($userId));
    }

    public function testUserUpdate()
    {
        // First create a user
        $userId = 'newUser' . time();
        $utterance = new WebchatTextUtterance();
        $utterance->setUserId($userId);

        $user = $this->userService->createOrUpdateUser($utterance);
        $this->assertTrue($this->userService->userExists($userId));

        // Let us get the uid of the user and the timestamp that was set first time
        $uid = $user->getUid();
        $timestamp = $user->getAttribute('timestamp');
        $this->assertTrue(isset($uid));
        $this->assertTrue(isset($timestamp));

        $user2 = $this->userService->createOrUpdateUser($utterance);
        $this->assertTrue($user2->getUid() == $user->getUid());
        $this->assertTrue($user2->getAttribute('timestamp')->getValue() != $user->getAttribute('timestamp')->getValue());
    }

    public function testAssociatingStoredConversationToUser()
    {
        // First create a user
        $userId = 'abc123a';
        $utterance = new WebchatTextUtterance();
        $utterance->setUserId($userId);

        /* @var \OpenDialogAi\Core\Conversation\ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $conversationData = ConversationQueryFactory::getConversationTemplateIds($this->client)[0];

        // Get the conversation so we can attach to the user

        $conversation = ConversationQueryFactory::getConversationFromDgraph($conversationData['uid'], $this->client, true);
        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->isHavingConversation());
    }

    public function testSettingACurrentIntent()
    {
        // First create a user
        $userId = 'abc123a';
        $utterance = new WebchatTextUtterance();
        $utterance->setUserId($userId);

        /* @var \OpenDialogAi\Core\Conversation\ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $conversationData = ConversationQueryFactory::getConversationTemplateIds($this->client)[0];

        // Get the conversation so we can attach to the user
        $conversation = ConversationQueryFactory::getConversationFromDgraph($conversationData['uid'], $this->client, true);
        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        /* @var Scene $scene */
        $scene = $user->getCurrentConversation()->getOpeningScenes()->first()->value;

        /* @var \OpenDialogAi\Core\Conversation\Intent $intent */
        $intent = $scene->getIntentsSaidByUser()->first()->value;
        $user->setCurrentIntent($intent);
        $this->userService->setCurrentIntent($user, $intent);

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $this->assertTrue($user->getCurrentIntent()->getId() == 'hello_bot');
    }

    public function testUnSettingACurrentIntent()
    {
        // First create a user
        $userId = 'abc123a';
        $utterance = new WebchatTextUtterance();
        $utterance->setUserId($userId);

        /* @var \OpenDialogAi\Core\Conversation\ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $conversationData = ConversationQueryFactory::getConversationTemplateIds($this->client)[0];

        // Get the conversation so we can attach to the user
        $conversation = ConversationQueryFactory::getConversationFromDgraph($conversationData['uid'], $this->client, true);
        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        /* @var Scene $scene */
        $scene = $user->getCurrentConversation()->getOpeningScenes()->first()->value;

        /* @var \OpenDialogAi\Core\Conversation\Intent $intent */
        $intent = $scene->getIntentsSaidByUser()->first()->value;
        $user->setCurrentIntent($intent);
        $this->userService->setCurrentIntent($user, $intent);

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $this->assertTrue($user->getCurrentIntent()->getId() == 'hello_bot');

        $this->userService->unsetCurrentIntent($user);

        $user = $this->userService->getUser($userId);
        $this->assertFalse($user->hasCurrentIntent());
    }
}
