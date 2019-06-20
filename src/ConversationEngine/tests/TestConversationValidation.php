<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ConversationEngine\Rules\ConversationYAML;
use OpenDialogAi\Core\Tests\TestCase;

class TestConversationValidation extends TestCase
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
}
