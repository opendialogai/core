<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\AttributeEngine\AttributeDoesNotExistException;
use OpenDialogAi\AttributeEngine\IntAttribute;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelToGraphConverter;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\ModelFacets;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\Graph\Edge\DirectedEdge;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

class UserServiceTest extends TestCase
{
    /* @var UserService */
    private $userService;

    /** @var ConversationStoreInterface */
    private $conversationStore;

    public function setUp(): void
    {
        parent::setUp();

        $this->setConfigValue(
            'opendialog.context_engine.custom_attributes',
            [
                'testAttr' => IntAttribute::class
            ]
        );

        $this->conversationStore = resolve(ConversationStoreInterface::class);
        $this->userService = resolve(UserService::class);
    }

    /**
     * @requires DGRAPH
     *
     * @throws FieldNotSupported
     */
    public function testUserCreation()
    {
        $this->activateConversation($this->conversation1());

        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        $this->assertNotTrue($this->userService->userExists($userId));

        $this->userService->createOrUpdateUser($utterance);

        $this->assertTrue($this->userService->userExists($userId));
    }

    /**
     * @requires DGRAPH
     */
    public function testUserUpdate()
    {
        $this->activateConversation($this->conversation1());

        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        $user = $this->userService->createOrUpdateUser($utterance);
        $this->assertTrue($this->userService->userExists($userId));

        $firstName = $user->getUserAttribute('first_name');
        $this->assertTrue(isset($firstName));

        $utterance->getUser()->setFirstName('updated');

        $user2 = $this->userService->createOrUpdateUser($utterance);
        $this->assertEquals($user2->getUid(), $user->getUid());
        $this->assertNotEquals($user2->getUserAttribute('first_name')->getValue(), $firstName);
    }

    /**
     * @requires DGRAPH
     */
    public function testAssociatingStoredConversationToUser()
    {
        $this->activateConversation($this->conversation1());

        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        /* @var ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $conversationQuery = DGraphConversationQueryFactory::getConversationTemplateIds();
        $conversationResponse = resolve(DGraphClient::class)->query($conversationQuery);

        $conversationConverter = resolve(EIModelToGraphConverter::class);

        $conversationModel = $this->conversationStore->getEIModelConversation($conversationResponse->getData()[0]['uid']);

        /** @var Conversation $conversationForCloning */
        $conversationForCloning = $conversationConverter->convertConversation($conversationModel, true);

        /** @var Conversation $conversationForConnecting */
        $conversationForConnecting = $conversationConverter->convertConversation($conversationModel, false);

        $this->userService->setCurrentConversation($user, $conversationForCloning, $conversationForConnecting);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->isHavingConversation());

        $conversationUserModel = $this->conversationStore->getEIModelConversation($user->getCurrentConversationUid());

        /** @var Conversation $conversationUser */
        $conversationUser = $conversationConverter->convertConversation($conversationUserModel);

        $this->assertEquals($conversationForCloning->getId(), $conversationUser->getId());
        $this->assertEquals(Model::CONVERSATION_USER, $conversationUser->getAttribute(Model::EI_TYPE)->getValue());

        // Ensure that the conversation was properly cloned by checking that the template & user conversation UIDs are different
        $this->assertNotEquals($conversationForCloning->getUid(), $conversationUser->getUid());
        $this->assertEquals($user->getCurrentConversationUid(), $conversationUser->getUid());

        /** @var Scene $openingScene */
        $openingScene = $conversationForCloning->getOpeningScenes()->first()->value;

        /** @var Scene $openingUserScene */
        $openingUserScene = $conversationUser->getOpeningScenes()->first()->value;

        $this->assertNotEquals($openingScene->getUid(), $openingUserScene->getUid());
    }

    /**
     * @requires DGRAPH
     */
    public function testSettingACurrentIntent()
    {
        $userId = $this->setUpConversationAndCurrentIntent();

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $currentIntent = $this->conversationStore->getEIModelIntentByUid($user->getCurrentIntentUid());
        $this->assertEquals('hello_bot', $currentIntent->getIntentId());
    }

    /**
     * @requires DGRAPH
     *
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException
     */
    public function testUnSettingACurrentIntent()
    {
        $userId = $this->setUpConversationAndCurrentIntent();

        // Get the user back from dgraph
        $user = $this->userService->getUser($userId);

        $this->assertTrue($user->hasCurrentIntent());
        $intent = $this->conversationStore->getEIModelIntentByUid($user->getCurrentIntentUid());
        $this->assertEquals('hello_bot', $intent->getIntentId());

        $this->userService->unsetCurrentIntent($user);

        $user = $this->userService->getUser($userId);
        $this->assertFalse($user->hasCurrentIntent());
    }

    /**
     * @requires DGRAPH
     */
    public function testCustomAttributesArePersistedAndQueryable()
    {
        $this->activateConversation($this->conversation1());

        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        // Ensure value is not currently persisted
        $userBeforePersisting = $this->userService->getUser($userId);

        $caught = false;
        try {
            $userBeforePersisting->getUserAttribute('testAttr');
        } catch (AttributeDoesNotExistException $e) {
            $caught = true;
        }
        $this->assertTrue($caught);

        // Set testAttr value on User
        $utterance->getUser()->setCustomParameters([ 'testAttr' => 100 ]);

        /* @var ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        /** @var IntAttribute $testAttr */
        $testAttr = null;

        // Ensure value is on the User object
        try {
            $testAttr = $user->getUserAttribute('testAttr');
        } catch (AttributeDoesNotExistException $e) {
            $this->fail($e);
        }

        $this->assertEquals(100, $testAttr->getValue());

        $user = $this->userService->updateUser($user);
        $countBeforeUpdating = $user->getAllUserAttributes()->count();

        // Ensure the attribute is correctly updated
        $utterance = UtteranceGenerator::generateTextUtterance('', $utterance->getUser());
        $utterance->getUser()->setCustomParameters([ 'testAttr' => 200 ]);

        /** @var ChatbotUser $userAfterUpdating */
        $userAfterUpdating = $this->userService->createOrUpdateUser($utterance);

        // Ensure value is on the User object
        try {
            $testAttr = $userAfterUpdating->getUserAttribute('testAttr');
        } catch (AttributeDoesNotExistException $e) {
            $this->fail($e);
        }

        $this->assertEquals(200, $testAttr->getValue());
        $this->assertEquals($countBeforeUpdating, $userAfterUpdating->getAllUserAttributes()->count());
    }

    /**
     * @requires DGRAPH
     */
    public function testFollowedByAndPrecededBy()
    {
        $userId = $this->setUpConversationAndCurrentIntent();

        $user = $this->userService->getUser($userId);

        $client = resolve(DGraphClient::class);
        $originalIntentUid = $user->getCurrentIntentUid();

        $response = $client->query((new DGraphQuery())->uid($originalIntentUid)->setQueryGraph([
            Model::UID,
            Model::FOLLOWED_BY => [
                DGraphQuery::WITH_FACETS,
                Model::UID
            ]
        ]));

        $this->assertArrayNotHasKey(Model::FOLLOWED_BY, $response->getData()[0]);

        $conversation = $this->userService->getCurrentConversation($userId);

        /** @var Scene $scene */
        $scene = $conversation->getOpeningScenes()->first()->value;

        /** @var Intent $intent */
        $intent = $scene->getIntentsSaidByBotInOrder()->first()->value;

        $this->userService->setCurrentIntent($user, $intent);

        $response = $client->query((new DGraphQuery())->uid($originalIntentUid)->setQueryGraph([
            Model::UID,
            Model::FOLLOWED_BY => [
                DGraphQuery::WITH_FACETS,
                Model::UID
            ]
        ]));

        $this->assertArrayHasKey(Model::FOLLOWED_BY, $response->getData()[0]);
        $this->assertArrayHasKey(ModelFacets::facet(Model::FOLLOWED_BY, ModelFacets::CREATED_AT), $response->getData()[0]);

        $response = $client->query((new DGraphQuery())->uid($user->getCurrentIntentUid())->setQueryGraph([
            Model::UID,
            Model::PRECEDED_BY => [
                DGraphQuery::WITH_FACETS,
                Model::UID
            ]
        ]));

        $this->assertArrayHasKey(Model::PRECEDED_BY, $response->getData()[0]);
        $this->assertArrayHasKey(ModelFacets::facet(Model::PRECEDED_BY, ModelFacets::CREATED_AT), $response->getData()[0]);

        // Ensure the conversation graph has the edge + facets too
        $conversation = $this->userService->getCurrentConversation($userId);

        /** @var Scene $scene */
        $scene = $conversation->getOpeningScenes()->first()->value;

        /** @var Intent $intent */
        $intent = $scene->getIntentsSaidByUserInOrder()->first()->value;

        $followedByEdgeSet = $intent->getOutgoingEdgesWithRelationship(Model::FOLLOWED_BY);

        $this->assertNotFalse($followedByEdgeSet);
        $this->assertNotNull($followedByEdgeSet->getEdges());

        /** @var DirectedEdge $edge */
        $edge = $followedByEdgeSet->getEdges()[0];
        $this->assertEquals('hello_bot', $edge->getFromNode()->getId());
        $this->assertEquals('hello_user', $edge->getToNode()->getId());
        $this->assertTrue($edge->hasFacets());
        $this->assertCount(1, $edge->getFacets());
        $this->assertTrue($edge->getFacets()->hasKey(ModelFacets::CREATED_AT));

        $this->assertTrue($intent->hasFollowedBy());
        $this->assertEquals('hello_user', $intent->getFollowedBy()->getId());
    }

    /**
     * @requires DGRAPH
     */
    public function testGetUserTypeNew()
    {
        $this->assertEquals(ChatbotUser::NEW_USER, $this->userService->getUserType('newuser'));
    }

    /**
     * @requires DGRAPH
     */
    public function testGetUserTypeOngoing()
    {
        $userId = $this->setUpConversationAndCurrentIntent();
        $this->assertEquals(ChatbotUser::ONGOING_USER, $this->userService->getUserType($userId));
    }

    /**
     * @requires DGRAPH
     */
    public function testGetUserTypeReturning()
    {
        $this->activateConversation($this->noMatchConversation());

        $utterance = UtteranceGenerator::generateChatOpenUtterance('WELCOME');
        $controller = resolve(OpenDialogController::class);
        $controller->runConversation($utterance);
        $this->assertEquals(ChatbotUser::RETURNING_USER, $this->userService->getUserType($utterance->getUserId()));
    }

    /**
     * @return string
     * @throws FieldNotSupported
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException
     */
    private function setUpConversationAndCurrentIntent(): string
    {
        $this->activateConversation($this->conversation1());

        $utterance = UtteranceGenerator::generateTextUtterance();
        $userId = $utterance->getUser()->getId();

        /* @var ChatbotUser $user */
        $user = $this->userService->createOrUpdateUser($utterance);

        $this->assertFalse($user->isHavingConversation());
        $this->assertFalse($user->hasCurrentIntent());

        $client = resolve(DGraphClient::class);
        $conversationQuery = DGraphConversationQueryFactory::getConversationTemplateIds();
        $conversationResponse = $client->query($conversationQuery);

        $conversationStore = resolve(ConversationStoreInterface::class);
        $conversationConverter = resolve(EIModelToGraphConverter::class);

        $conversationModel = $conversationStore->getEIModelConversation($conversationResponse->getData()[0]['uid']);

        /** @var Conversation $conversationForCloning */
        $conversationForCloning = $conversationConverter->convertConversation($conversationModel, true);

        /** @var Conversation $conversationForConnecting */
        $conversationForConnecting = $conversationConverter->convertConversation($conversationModel, false);

        $this->userService->setCurrentConversation($user, $conversationForCloning, $conversationForConnecting);

        // Now let's retrieve this user
        $user = $this->userService->getUser($userId);

        /* @var Scene $scene */
        $scene = $this->userService->getCurrentConversation($userId)->getOpeningScenes()->first()->value;

        /* @var Intent $intent */
        $intent = $scene->getIntentsSaidByUser()->first()->value;
        $this->userService->setCurrentIntent($user, $intent);

        return $userId;
    }
}
