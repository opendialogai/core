<?php

namespace OpenDialogAi\Core\ResponseEngine\tests\Formatters;

use OpenDialogAi\ResponseEngine\Message\MessageFormatterInterface;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatEmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatFormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatListMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatLongTextMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatRichMessage;

class DummyFormatter implements MessageFormatterInterface
{
    protected static $name = 'badly_formed';

    public function getMessages(string $markup): array
    {
        //
    }

    public function generateButtonMessage(array $template): WebchatButtonMessage
    {
        //
    }

    public function generateEmptyMessage(): WebchatEmptyMessage
    {
        //
    }

    public function generateFormMessage(array $template): WebchatFormMessage
    {
        //
    }

    public function generateImageMessage(array $template): WebchatImageMessage
    {
        //
    }

    public function generateListMessage(array $template): WebchatListMessage
    {
        //
    }

    public function generateLongTextMessage(array $template): WebchatLongTextMessage
    {
        //
    }

    public function generateRichMessage(array $template): WebchatRichMessage
    {
        //
    }

    public function generateTextMessage(array $template): OpenDialogMessage
    {
        //
    }
}
