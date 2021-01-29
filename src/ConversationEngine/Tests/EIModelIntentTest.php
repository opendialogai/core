<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use Exception;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationQueryFactoryInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelScene;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelVirtualIntent;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;

class EIModelIntentTest extends TestCase
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
    public function testCanGetIntent()
    {
        $this->activateConversation($this->conversation1());

        $conversation = Conversation::where('name', 'hello_bot_world')->first();
        $query = $this->queryFactory::getConversationFromDGraphWithUid($conversation->graph_uid);
        $response = $this->dGraph->query($query);

        try {
            /* @var EIModelConversation $conversationModel */
            $conversationModel = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);
        } catch (Exception $e) {
            $this->fail($e);
        }

        /* @var EIModelScene $openingScene */
        $openingScene = $conversationModel->getOpeningScenes()->first();

        /* @var EIModelIntent $firstIntent */
        $firstIntent = $openingScene->getIntents()->first();

        $intentUid = $firstIntent->getIntentUid();
        $query = $this->queryFactory::getIntentByUid($intentUid);
        $response = $this->dGraph->query($query);

        try {
            /* @var EIModelIntent $conversationModel */
            $intent = $this->eiModelCreator->createEIModel(EIModelIntent::class, $response->getData()[0]);
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertEquals($intentUid, $intent->getIntentUid());
    }

    /**
     * @requires DGRAPH
     */
    public function testCanGetIntentAndVirtualIntent()
    {
        $this->activateConversation($this->conversation1());

        $this->createConversationWithVirtualIntent();

        $conversation = Conversation::where('name', 'with_virtual_intent')->first();
        $query = $this->queryFactory::getConversationFromDGraphWithUid($conversation->graph_uid);
        $response = $this->dGraph->query($query);

        try {
            /* @var EIModelConversation $conversationModel */
            $conversationModel = $this->eiModelCreator->createEIModel(EIModelConversation::class, $response->getData()[0]);
        } catch (Exception $e) {
            $this->fail($e);
        }

        /* @var EIModelScene $openingScene */
        $openingScene = $conversationModel->getOpeningScenes()->first();

        /* @var EIModelIntent $secondIntent */
        $secondIntent = $openingScene->getIntents()->get(1);

        /** @var EIModelVirtualIntent $virtualIntent */
        $virtualIntent = $secondIntent->getVirtualIntent();
        $this->assertNotNull($virtualIntent);
        $this->assertInstanceOf(EIModelVirtualIntent::class, $virtualIntent);
        $this->assertEquals('intent.app.continue', $virtualIntent->getId());
    }
}
