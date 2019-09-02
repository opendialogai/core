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

    public function testConversationNameSameAsIntent()
    {
        $rule = new ConversationYAML();

        $invalidYaml = /** yaml */
            <<< EOT
conversation:
  id: conversation_1
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.core.hello
        - b: 
            i: conversation_1
            completes: true
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            confidence: 1
            action: action.core.example
        - b: 
            i: doing_dandy
            action: action.core.example
            completes: true
EOT;

        $this->assertFalse($rule->passes('test', $invalidYaml));
        $this->assertStringContainsString('Conversation can not have the same name as an intent', $rule->message());

        $validYaml = /** yaml */
            <<< EOT
conversation:
  id: conversation_1
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.core.hello
        - b: 
            i: intent.core.hello_response
            completes: true
    scene2:
      intents:
        - u: 
            i: how_are_you
            interpreter: interpreter.core.callbackInterpreter
            confidence: 1
            action: action.core.example
        - b: 
            i: doing_dandy
            action: action.core.example
            completes: true
EOT;

        $this->assertTrue($rule->passes('test', $validYaml));
    }
}
