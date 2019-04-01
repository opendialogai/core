<?php

namespace OpenDialogAi\ResponseEngine\Message;

/**
 * Message formatter interface.
 */
interface MessageFormatterInterface
{
    public function getMessages(string $markup);

    public function generateButtonMessage(array $template);

    public function generateEmptyMessage();

    public function generateFormMessage(array $template);

    public function generateImageMessage(array $template);

    public function generateListMessage(array $template);

    public function generateLongTextMessage(array $template);

    public function generateTextMessage(array $template);
}
