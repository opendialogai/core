<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class CompletingConversationTest extends TestCase
{
    /**
     * @requires DGRAPH
     */
    public function testCompletingIntents()
    {
        $this->activateConversation($this->conver1());
        $this->activateConversation($this->conver2());
        $this->activateConversation($this->conversation4());

        $this->setSupportedCallbacks([
            'hello_bot' => 'intent.core.hello_bot',
            'hello_again_bot' => 'intent.core.hello_again_bot'
        ]);

        /** @var OpenDialogController $odController */
        $odController = app()->make(OpenDialogController::class);

        $utterance1 = UtteranceGenerator::generateChatOpenUtterance('hello_bot');

        $response1 = $odController->runConversation($utterance1);

        $this->assertStringContainsString('hello_human', $response1->getMessages()[0]->getText());

        $utterance2 = UtteranceGenerator::generateChatOpenUtterance('hello_again_bot', $utterance1->getUser());

        $response2 = $odController->runConversation($utterance2);

        $this->assertStringContainsString('hello_again_human', $response2->getMessages()[0]->getText());
    }

    public function conver1()
    {
        return /** @lang yaml */
            <<<EOT
conversation:
  id: conversation_1
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.core.hello_bot
        - b: 
            i: intent.core.hello_human
            completes: true
EOT;
    }

    public function conver2()
    {
        return /** @lang yaml */
            <<<EOT
conversation:
  id: conversation_2
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.core.hello_again_bot
        - b: 
            i: intent.core.hello_again_human
            completes: true
EOT;
    }
}
