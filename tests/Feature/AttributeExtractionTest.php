<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
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

    public function testInit()
    {
        $this->assertTrue(true);
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
