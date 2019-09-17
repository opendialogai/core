<?php

namespace OpenDialogAi\Core\ResponseEngine\tests\Formatters;

use OpenDialogAi\ResponseEngine\Message\MessageFormatterInterface;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\Core\ResponseEngine\Message\ButtonMessage;
use OpenDialogAi\Core\ResponseEngine\Message\EmptyMessage;
use OpenDialogAi\Core\ResponseEngine\Message\FormMessage;
use OpenDialogAi\Core\ResponseEngine\Message\ImageMessage;
use OpenDialogAi\Core\ResponseEngine\Message\ListMessage;
use OpenDialogAi\Core\ResponseEngine\Message\LongTextMessage;
use OpenDialogAi\Core\ResponseEngine\Message\RichMessage;

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
