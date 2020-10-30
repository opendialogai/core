<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use Exception;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationQueryFactoryInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelScene;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;

class EIModelConversationTest extends TestCase
{
    /**
     * @var EIModelCreator
     */
    private $eiModelCreator;

    /**
     * @var DGraphClient
     */
    private $dGraph;

    /**
     * @var ConversationQueryFactoryInterface
     */
    private $queryFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->eiModelCreator = app()->make(EIModelCreator::class);
        $this->dGraph = app()->make(DGraphClient::class);
        $this->queryFactory = app()->make(ConversationQueryFactoryInterface::class);

        $attributes = ['test' => IntAttribute::class];
        AttributeResolver::registerAttributes($attributes);
    }

    /**
     * @requires DGRAPH
     */
    public function testCanGetConversation()
    {
        $this->activateConversation($this->conversation1());
        $this->activateConversation($this->conversation2());
        $this->activateConversation($this->conversation3());

        $conversation = Conversation::where('name', 'hello_bot_world')->first();
        $query = $this->queryFactory::getConversationFromDGraphWithUid($conversation->graph_uid);
        $response = $this->dGraph->query($query);

        try {
            /* @var EIModelConversation $conversationModel */
            $conversationModel = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertEquals($conversation->graph_uid, $conversationModel->getUid());
        $this->assertEquals("hello_bot_world", $conversationModel->getId());
        $this->assertEquals(Model::CONVERSATION_TEMPLATE, $conversationModel->getEiType());
        $this->assertNotNull($conversationModel->getConditions());
        $this->assertEquals(2, count($conversationModel->getConditions()));
        $this->assertNotNull($conversationModel->getOpeningScenes());
        $this->assertInstanceOf(EIModelScene::class, $conversationModel->getOpeningScenes()->first());
        $this->assertEquals(1, count($conversationModel->getOpeningScenes()));
        $this->assertNotNull($conversationModel->getScenes());
        $this->assertInstanceOf(EIModelScene::class, $conversationModel->getScenes()->first());
        $this->assertEquals(3, count($conversationModel->getScenes()));

        /* @var EIModelScene $openingScene */
        $openingScene = $conversationModel->getOpeningScenes()->first();

        $this->assertEquals(3, count($openingScene->getIntents()));
        $this->assertEquals(1, count($openingScene->getUserSaysIntents()));
        $this->assertEquals(2, count($openingScene->getBotSaysAcrossScenesIntents()));

        /* @var EIModelIntent $firstIntent */
        $firstIntent = $openingScene->getUserSaysIntents()->first();

        $this->assertNotNull($firstIntent->getAction());
        $this->assertEquals("action.core.example", $firstIntent->getAction()->key);

        /* @var EIModelIntent $thirdIntent */
        $thirdIntent = $openingScene->getBotSaysAcrossScenesIntents()->get(1);
        $this->assertNotNull($thirdIntent->getNextScene());
    }
}
