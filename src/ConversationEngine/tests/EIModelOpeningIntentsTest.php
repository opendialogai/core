<?php


namespace OpenDialogAi\ConversationEngine\tests;


use Ds\Map;
use Exception;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationQueryFactoryInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelOpeningIntents;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;

class EIModelOpeningIntentsTest extends TestCase
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

        $this->activateConversation($this->conversation1());
        $this->activateConversation($this->conversation2());
        $this->activateConversation($this->conversation3());
        $this->activateConversation($this->conversation4());
    }

    public function testReturnsSimpleSingleOpeningIntents()
    {
        // Test only with conversations that have one user intent in their opening scenes
        $query = $this->queryFactory::getAllOpeningIntents();
        $response = $this->dGraph->query($query);

        try {
            /* @var EIModelOpeningIntents $allOpeningIntentsModel */
            $allOpeningIntentsModel = $this->eiModelCreator->createEIModel(EIModelOpeningIntents::class, $response->getData());
        } catch (Exception $e) {
            $this->fail($e);
        }

        /* @var Map $openingIntents */
        $openingIntents = $allOpeningIntentsModel->getIntents();
        $this->assertCount(4, $openingIntents);
        $this->assertEquals("hello_bot", $openingIntents->skip(0)->value->getIntentId());
        $this->assertEquals("howdy_bot", $openingIntents->skip(1)->value->getIntentId());
        $this->assertEquals("top_of_the_morning_bot", $openingIntents->skip(2)->value->getIntentId());
        $this->assertEquals("intent.core.NoMatch", $openingIntents->skip(3)->value->getIntentId());
    }

    public function testReturnsComplexSingleOpeningIntents()
    {
        // Test with a conversation that has many user intents in the opening scene
        $complexConversationYaml = <<<EOT
conversation:
  id: complex_convo
  scenes:
    opening_scene:
      intents:
        - u: 
            i: order_pizza
        - b: 
            i: ask_topping
        - u: 
            i: send_topping
        - b: 
            i: ask_size
        - u: 
            i: send_size
        - b: 
            i: complete_order
            completes: true
EOT;
        $this->activateConversation($complexConversationYaml);

        $query = $this->queryFactory::getAllOpeningIntents();
        $response = $this->dGraph->query($query);

        try {
            /* @var EIModelOpeningIntents $allOpeningIntentsModel */
            $allOpeningIntentsModel = $this->eiModelCreator->createEIModel(EIModelOpeningIntents::class, $response->getData());
        } catch (Exception $e) {
            $this->fail($e);
        }

        /* @var Map $openingIntents */
        $openingIntents = $allOpeningIntentsModel->getIntents();
        $this->assertEquals(5, $openingIntents->count());
        $this->assertEquals("hello_bot", $openingIntents->skip(0)->value->getIntentId());
        $this->assertEquals("howdy_bot", $openingIntents->skip(1)->value->getIntentId());
        $this->assertEquals("top_of_the_morning_bot", $openingIntents->skip(2)->value->getIntentId());
        $this->assertEquals("intent.core.NoMatch", $openingIntents->skip(3)->value->getIntentId());
        $this->assertEquals("order_pizza", $openingIntents->skip(4)->value->getIntentId());
    }

    public function testReturnsComplexMultipleOpeningIntents()
    {
        // Test with a conversation that has many user intents in the opening scene and many opening user intents
        $complexConversationYaml = <<<EOT
conversation:
  id: complex_convo
  scenes:
    opening_scene:
      intents:
        - u: 
            i: order_pizza
        - u: 
            i: rate_pizza
            scene: rate_pizza
        - b: 
            i: ask_topping
        - u: 
            i: send_topping
        - b: 
            i: ask_size
        - u: 
            i: send_size
        - b: 
            i: complete_order
            completes: true
    rate_pizza:
      intents:
        - b: 
            i: ask_rating
        - b: 
            i: send_rating
        - b: 
            i: complete_rating
            completes: true
EOT;
        $this->activateConversation($complexConversationYaml);

        $query = $this->queryFactory::getAllOpeningIntents();
        $response = $this->dGraph->query($query);

        try {
            /* @var EIModelOpeningIntents $allOpeningIntentsModel */
            $allOpeningIntentsModel = $this->eiModelCreator->createEIModel(EIModelOpeningIntents::class, $response->getData());
        } catch (Exception $e) {
            $this->fail($e);
        }

        /* @var Map $openingIntents */
        $openingIntents = $allOpeningIntentsModel->getIntents();
        $this->assertEquals(6, $openingIntents->count());
        $this->assertEquals("hello_bot", $openingIntents->skip(0)->value->getIntentId());
        $this->assertEquals("howdy_bot", $openingIntents->skip(1)->value->getIntentId());
        $this->assertEquals("top_of_the_morning_bot", $openingIntents->skip(2)->value->getIntentId());
        $this->assertEquals("intent.core.NoMatch", $openingIntents->skip(3)->value->getIntentId());
        $this->assertEquals("order_pizza", $openingIntents->skip(4)->value->getIntentId());
        $this->assertEquals("rate_pizza", $openingIntents->skip(5)->value->getIntentId());
    }
}
