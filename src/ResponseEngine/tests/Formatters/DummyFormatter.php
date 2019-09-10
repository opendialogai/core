<?php

namespace OpenDialogAi\Core\ResponseEngine\tests\Formatters;

use OpenDialogAi\ResponseEngine\Message\MessageFormatterInterface;

class DummyFormatter implements MessageFormatterInterface
{
    protected static $name = 'badly_formed';

    public function getMessages(string $markup)
    {
        //
    }

    public function generateButtonMessage(array $template)
    {
        //
    }

    public function generateEmptyMessage()
    {
        //
    }

    public function generateFormMessage(array $template)
    {
        //
    }

    public function generateImageMessage(array $template)
    {
        //
    }

    public function generateListMessage(array $template)
    {
        //
    }

    public function generateLongTextMessage(array $template)
    {
        //
    }

    public function generateRichMessage(array $template)
    {
        //
    }

    public function generateTextMessage(array $template)
    {
        //
    }
}
