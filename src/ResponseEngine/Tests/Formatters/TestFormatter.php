<?php

namespace OpenDialogAi\Core\ResponseEngine\Tests\Formatters;

use OpenDialogAi\ResponseEngine\Formatters\BaseMessageFormatter;
use OpenDialogAi\ResponseEngine\Message\AutocompleteMessage;
use OpenDialogAi\ResponseEngine\Message\ButtonMessage;
use OpenDialogAi\ResponseEngine\Message\DatePickerMessage;
use OpenDialogAi\ResponseEngine\Message\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\FormMessage;
use OpenDialogAi\ResponseEngine\Message\FullPageFormMessage;
use OpenDialogAi\ResponseEngine\Message\FullPageRichMessage;
use OpenDialogAi\ResponseEngine\Message\HandToSystemMessage;
use OpenDialogAi\ResponseEngine\Message\ImageMessage;
use OpenDialogAi\ResponseEngine\Message\ListMessage;
use OpenDialogAi\ResponseEngine\Message\LongTextMessage;
use OpenDialogAi\ResponseEngine\Message\MetaMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\Message\RichMessage;

class TestFormatter extends BaseMessageFormatter
{
    protected static string $componentId = 'formatter.test.test';

    protected static ?string $componentName = 'Example formatter';
    protected static ?string $componentDescription = 'Just an example formatter.';

    public function getMessages(string $markup): OpenDialogMessages
    {
        //
    }

    public function generateAutocompleteMessage(array $template): AutocompleteMessage
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

    public function generateFullPageFormMessage(array $template): FullPageFormMessage
    {
        //
    }

    public function generateHandToSystemMessage(array $template): HandToSystemMessage
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

    public function generateMetaMessage(array $template): MetaMessage
    {
        //
    }

    public function generateRichMessage(array $template): RichMessage
    {
        //
    }

    public function generateFullPageRichMessage(array $template): FullPageRichMessage
    {
        //
    }

    public function generateTextMessage(array $template): OpenDialogMessage
    {
        //
    }

    public function generateDatePickerMessage(array $template): DatePickerMessage
    {
        // TODO: Implement generateDatePickerMessage() method.
    }
}
