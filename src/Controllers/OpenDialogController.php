<?php

namespace OpenDialogAi\Core\Controllers;

use OpenDialogAi\Core\Utterances\UtteranceInterface;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;

class OpenDialogController
{
    public function runConversation(UtteranceInterface $utterance)
    {
        return (new WebChatMessage())->setText("Hello");
    }
}
