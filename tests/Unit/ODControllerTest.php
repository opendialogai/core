<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Utterances\Webchat\WebchatTextUtterance;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;

class ODControllerTest extends TestCase
{
    public function testBinding()
    {
        $this->assertEquals(OpenDialogController::class, get_class($this->app->make(OpenDialogController::class)));
    }

    public function testGetMessage()
    {
        /** @var OpenDialogController $odController */
        $odController = $this->app->make(OpenDialogController::class);

        $utterance = new WebchatTextUtterance();

        $message = $odController
            ->runConversation($utterance);

        $this->assertEquals(WebChatMessage::class, get_class($message));
    }
}
