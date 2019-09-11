<?php

namespace OpenDialogAi\Core\ResponseEngine\tests\Formatters;

use OpenDialogAi\ResponseEngine\Message\MessageFormatterInterface;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\ButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\FormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\ImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\ListMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\LongTextMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\RichMessage;

class DummyFormatter implements MessageFormatterInterface
{
    protected static $name = 'badly_formed';

    public function getMessages(string $markup): array
    {
        //
    }

    public function generateButtonMessage(array $template): ButtonMessage
    {
        //
    }

    public function generateEmptyMessage(): EmptyMessage
    {
        //
    }

    public function generateFormMessage(array $template): FormMessage
    {
        //
    }

    public function generateImageMessage(array $template): ImageMessage
    {
        //
    }

    public function generateListMessage(array $template): ListMessage
    {
        //
    }

    public function generateLongTextMessage(array $template): LongTextMessage
    {
        //
    }

    public function generateRichMessage(array $template): RichMessage
    {
        //
    }

    public function generateTextMessage(array $template): OpenDialogMessage
    {
        //
    }
}
