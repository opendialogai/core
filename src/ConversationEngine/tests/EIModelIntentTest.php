<?php


namespace OpenDialogAi\ConversationEngine\tests;


use Exception;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationQueryFactoryInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelScene;
use OpenDialogAi\Core\Attribute\IntAttribute;
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

        $this->activateConversation($this->conversation1());
    }

    public function testCanGetIntent() {
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
}
