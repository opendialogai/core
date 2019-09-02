<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ConversationEngine\Rules\ConversationYAML;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationValidationTest extends TestCase
{
    public function testConversationValidation()
    {
        $rule = new ConversationYAML();

        $invalidYaml = /** yaml */
            <<< EOT
conversation:
EOT;
        $this->assertFalse($rule->passes('test', $invalidYaml));
        $this->assertStringContainsString('Conversation have must an ID', $rule->message());


        $invalidYaml = /** yaml */
            <<< EOT
conversation:
  id: hello
EOT;
        $this->assertFalse($rule->passes('test', $invalidYaml));
        $this->assertStringContainsString('Conversation must have at least 1 scene', $rule->message());

        $invalidYaml = /** yaml */
            <<< EOT
conversation: id: hello
EOT;
        $this->assertFalse($rule->passes('test', $invalidYaml));
        $this->assertStringContainsString('Invalid YAML', $rule->message());
    }

    public function testIncomingIntentAndOutgoingIntentSameName()
    {
        $rule = new ConversationYAML();

        $invalidYaml = /** yaml */
            <<<EOT
conversation:
  id: hello_bot_world
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
            interpreter: interpreter.core.callbackInterpreter
        - b: 
            i: hello_bot
            completes: true
EOT;
        $this->assertFalse($rule->passes('test', $invalidYaml));
        $this->assertStringContainsString('Incoming intent and Outgoing intent can not have the same name', $rule->message());
    }
}
