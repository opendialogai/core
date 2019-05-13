<?php

namespace OpenDialogAi\ResponseEngine\Message;

/**
 * Message formatter interface.
 */
interface MessageFormatterInterface
{
    public const ATTRIBUTE_MESSAGE = 'attribute-message';
    public const BUTTON_MESSAGE    = 'button-message';
    public const IMAGE_MESSAGE     = 'image-message';
    public const TEXT_MESSAGE      = 'text-message';
    public const EMPTY_MESSAGE     = 'empty-message';

    public function getMessages(string $markup);

    public function generateButtonMessage(array $template);

    public function generateEmptyMessage();

    public function generateFormMessage(array $template);

    public function generateImageMessage(array $template);

    public function generateListMessage(array $template);

    public function generateLongTextMessage(array $template);

    public function generateTextMessage(array $template);
}
