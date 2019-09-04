<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

class UserServiceTest extends TestCase
{
    /* @var UserService */
    private $userService;

    /* @var DGraphClient */
    private $client;

    /** @var ConversationStoreInterface */
    private $conversationStore;

    public function setUp(): void
    {
        parent::setUp();

        $this->conversationStore = $this->app->make(ConversationStoreInterface::class);
        $this->userService = $this->app->make(UserService::class);
        $this->client = $this->app->make(DGraphClient::class);
        $this->client->dropSchema();
        $this->client->initSchema();

        for ($i = 1; $i <= 4; $i++) {
            $conversationId = 'conversation' . $i;
            $this->publishConversation($this->$conversationId());
        }
    }

    /**
     * @throws FieldNotSupported
     */
    public function testUserCreation()
    {
        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        $this->assertNotTrue($this->userService->userExists($userId));

        $this->userService->createOrUpdateUser($utterance);

        $this->assertTrue($this->userService->userExists($userId));
    }

    public function testUserUpdate()
    {
        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        $user = $this->userService->createOrUpdateUser($utterance);
        $this->assertTrue($this->userService->userExists($userId));

        $firstName = $user->getAttribute('first_name');
        $this->assertTrue(isset($firstName));

        $utterance->getUser()->setFirstName('updated');

        $user2 = $this->userService->createOrUpdateUser($utterance);
        $this->assertEquals($user2->getUid(), $user->getUid());
        $this->assertNotEquals($user2->getAttribute('first_name')->getValue(), $firstName);
    }

    public function testAssociatingStoredConversationToUser()
    {
        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        /* @var ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $conversationData = DGraphConversationQueryFactory::getConversationTemplateIds($this->client)[0];

        $conversation = DGraphConversationQueryFactory::getConversationFromDGraphWithUid($conversationData['uid'], $this->client);
        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->isHavingConversation());
    }

    public function testSettingACurrentIntent()
    {
        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        /* @var ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $conversationData = DGraphConversationQueryFactory::getConversationTemplateIds($this->client)[0];

        // Get the conversation so we can attach to the user
        $conversation = DGraphConversationQueryFactory::getConversationFromDGraphWithUid($conversationData['uid'], $this->client, true);
        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        /* @var Scene $scene */
        $scene = $this->conversationStore->getConversation($user->getCurrentConversationUid(), false)->getOpeningScenes()->first()->value;

        /* @var \OpenDialogAi\Core\Conversation\Intent $intent */
        $intent = $scene->getIntentsSaidByUser()->first()->value;
        $this->userService->setCurrentIntent($user, $intent);

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $currentIntent = $this->conversationStore->getIntentByUid($user->getCurrentIntentUid());
        $this->assertEquals('hello_bot', $currentIntent->getId());
    }

    /**
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testUnSettingACurrentIntent()
    {
        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        /* @var ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $conversationData = DGraphConversationQueryFactory::getConversationTemplateIds($this->client)[0];

        // Get the conversation so we can attach to the user
        $conversation = DGraphConversationQueryFactory::getConversationFromDGraphWithUid($conversationData['uid'], $this->client, true);
        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        /* @var Scene $scene */
        $scene = $this->conversationStore->getConversation($user->getCurrentConversationUid(), false)->getOpeningScenes()->first()->value;

        /* @var \OpenDialogAi\Core\Conversation\Intent $intent */
        $intent = $scene->getIntentsSaidByUser()->first()->value;
        $this->userService->setCurrentIntent($user, $intent);

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $intent = $this->conversationStore->getIntentByUid($user->getCurrentIntentUid());
        $this->assertEquals('hello_bot', $intent->getId());

        $this->userService->unsetCurrentIntent($user);

        $user = $this->userService->getUser($userId);
        $this->assertFalse($user->hasCurrentIntent());
    }
}
