<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelToGraphConverter;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Model;
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

        $conversationQuery = DGraphConversationQueryFactory::getConversationTemplateIds();
        $conversationResponse = $this->client->query($conversationQuery);

        $conversationStore = app()->make(ConversationStoreInterface::class);
        $conversationConverter = app()->make(EIModelToGraphConverter::class);

        $conversationModel = $conversationStore->getEIModelConversation($conversationResponse->getData()[0]['uid']);

        /** @var \OpenDialogAi\Core\Conversation\Conversation $conversation */
        $conversation = $conversationConverter->convertConversation($conversationModel, true);
        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->isHavingConversation());

        $conversationUserModel = $conversationStore->getEIModelConversation($user->getCurrentConversationUid());

        /** @var \OpenDialogAi\Core\Conversation\Conversation $conversationUser */
        $conversationUser = $conversationConverter->convertConversation($conversationUserModel);

        $this->assertEquals($conversation->getId(), $conversationUser->getId());
        $this->assertEquals(Model::CONVERSATION_USER, $conversationUser->getAttribute(Model::EI_TYPE)->getValue());

        // Ensure that the conversation was properly cloned by checking that the template & user conversation UIDs are different
        $this->assertNotEquals($conversation->getUid(), $conversationUser->getUid());
        $this->assertEquals($user->getCurrentConversationUid(), $conversationUser->getUid());

        /** @var Scene $openingScene */
        $openingScene = $conversation->getOpeningScenes()->first()->value;

        /** @var Scene $openingUserScene */
        $openingUserScene = $conversationUser->getOpeningScenes()->first()->value;

        $this->assertNotEquals($openingScene->getUid(), $openingUserScene->getUid());
    }

    public function testSettingACurrentIntent()
    {
        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        /* @var ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        // Get the conversation so we can attach to the user
        $conversationQuery = DGraphConversationQueryFactory::getConversationTemplateIds();
        $conversationResponse = $this->client->query($conversationQuery);

        $conversationStore = app()->make(ConversationStoreInterface::class);
        $conversationConverter = app()->make(EIModelToGraphConverter::class);

        $conversationModel = $conversationStore->getEIModelConversation($conversationResponse->getData()[0]['uid']);
        $conversation = $conversationConverter->convertConversation($conversationModel);

        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        /* @var Scene $scene */
        $scene = $conversation->getOpeningScenes()->first()->value;

        /* @var \OpenDialogAi\Core\Conversation\Intent $intent */
        $intent = $scene->getIntentsSaidByUser()->first()->value;
        $this->userService->setCurrentIntent($user, $intent);

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $currentIntent = $this->conversationStore->getEIModelIntentByUid($user->getCurrentIntentUid());
        $this->assertEquals('hello_bot', $currentIntent->getIntentId());
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

        $conversationQuery = DGraphConversationQueryFactory::getConversationTemplateIds();
        $conversationResponse = $this->client->query($conversationQuery);

        $conversationStore = app()->make(ConversationStoreInterface::class);
        $conversationConverter = app()->make(EIModelToGraphConverter::class);

        $conversationModel = $conversationStore->getEIModelConversation($conversationResponse->getData()[0]['uid']);
        $conversation = $conversationConverter->convertConversation($conversationModel);

        $this->userService->setCurrentConversation($user, $conversation);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        /* @var Scene $scene */
        $scene = $conversation->getOpeningScenes()->first()->value;

        /* @var \OpenDialogAi\Core\Conversation\Intent $intent */
        $intent = $scene->getIntentsSaidByUser()->first()->value;
        $this->userService->setCurrentIntent($user, $intent);

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $intent = $this->conversationStore->getEIModelIntentByUid($user->getCurrentIntentUid());
        $this->assertEquals('hello_bot', $intent->getIntentId());

        $this->userService->unsetCurrentIntent($user);

        $user = $this->userService->getUser($userId);
        $this->assertFalse($user->hasCurrentIntent());
    }
}
