<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\OpeningIntent;
use OpenDialogAi\Core\Tests\TestCase;

class AttributeExtractionTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    public function setUp(): void
    {
        parent::setUp();
        $this->conversationEngine = $this->app->make(ConversationEngineInterface::class);

        $this->publishConversation($this->getExampleConversation());
    }

    public function testOpeningSceneCreated()
    {
        $conversationStore = $this->conversationEngine->getConversationStore();
        $openingIntents = $conversationStore->getAllOpeningIntents();

        $this->assertCount(1, $openingIntents);

        /** @var OpeningIntent $myNameIntent */
        $myNameIntent = $openingIntents->first()->value;
        $this->assertEquals('my_name_is', $myNameIntent->getIntentId());

        $expectedAttributes = $myNameIntent->getExpectedAttributes();

        $this->assertCount(2, $expectedAttributes);
        $this->assertContains('user.first_name', $expectedAttributes->toArray());
        $this->assertContains('user.last_name', $expectedAttributes->toArray());
    }

    private function getExampleConversation()
    {
        return <<<EOT
conversation:
  id: attribute_test_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: my_name_is
            interpreter: interpreter.test.name
            expected_attributes:
                - id: user.first_name
                - id: user.last_name
        - b: 
            i: hello_user
EOT;
    }
}
