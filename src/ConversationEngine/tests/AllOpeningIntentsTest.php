<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\AllOpeningIntents;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;

class AllOpeningIntentsTest extends TestCase
{
    private $dGraph;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publishConversation($this->conversation1());
        $this->publishConversation($this->conversation2());
        $this->publishConversation($this->conversation3());
        $this->publishConversation($this->conversation4());

        $this->dGraph = app()->make(DGraphClient::class);
    }

    public function testReturnsSimpleSingleOpeningIntents()
    {
        // Test only with conversations that have one user intent in their opening scenes
        $allOpeningIntents = new AllOpeningIntents($this->dGraph);
        $openingIntents = $allOpeningIntents->getIntents();
        $this->assertEquals(4, $openingIntents->count());
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
        $this->publishConversation($complexConversationYaml);

        $allOpeningIntentsWithComplex = new AllOpeningIntents($this->dGraph);
        $openingIntents = $allOpeningIntentsWithComplex->getIntents();
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
        $this->publishConversation($complexConversationYaml);

        $allOpeningIntentsWithComplex = new AllOpeningIntents($this->dGraph);
        $openingIntents = $allOpeningIntentsWithComplex->getIntents();
        $this->assertEquals(6, $openingIntents->count());
        $this->assertEquals("hello_bot", $openingIntents->skip(0)->value->getIntentId());
        $this->assertEquals("howdy_bot", $openingIntents->skip(1)->value->getIntentId());
        $this->assertEquals("top_of_the_morning_bot", $openingIntents->skip(2)->value->getIntentId());
        $this->assertEquals("intent.core.NoMatch", $openingIntents->skip(3)->value->getIntentId());
        $this->assertEquals("order_pizza", $openingIntents->skip(4)->value->getIntentId());
        $this->assertEquals("rate_pizza", $openingIntents->skip(5)->value->getIntentId());
    }
}
