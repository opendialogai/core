<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class NoConversationsTest extends TestCase
{
    /**
     * @requires DGRAPH
     */
    public function testNoConversations()
    {
        $this->setSupportedCallbacks([
            'hello_bot' => 'intent.core.hello_bot',
            'hello_again_bot' => 'intent.core.hello_again_bot'
        ]);

        /** @var OpenDialogController $odController */
        $odController = app()->make(OpenDialogController::class);

        $utterance1 = UtteranceGenerator::generateChatOpenUtterance('hello_bot');

        $response1 = $odController->runConversation($utterance1);

        $this->assertStringContainsString('No conversations are defined or activated', $response1->getMessages()[0]->getText());
    }
}
