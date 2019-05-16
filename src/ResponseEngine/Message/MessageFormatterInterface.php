<?php

namespace OpenDialogAi\ResponseEngine\Message;

/**
 * Message formatter interface.
 */
interface MessageFormatterInterface
{
    // TYPES
    public const ATTRIBUTE_MESSAGE = 'attribute-message';
    public const BUTTON_MESSAGE    = 'button-message';
    public const IMAGE_MESSAGE     = 'image-message';
    public const TEXT_MESSAGE      = 'text-message';
    public const EMPTY_MESSAGE     = 'empty-message';

    // PROPERTIES
    public const BUTTONS                 = 'buttons';
    public const TEXT                    = 'text';
    public const CALLBACK                = 'callback';
    public const VALUE                   = 'value';
    public const LINK                    = 'link';
    public const SRC                     = 'src';
    public const LINK_NEW_TAB            = 'link_new_tab';
    public const TAB_SWITCH              = 'tab_switch';
    public const DISABLE_TEXT            = 'disable_text';
    public const CLEAR_AFTER_INTERACTION = 'clear_after_interaction';

    public function getMessages(string $markup);

    public function generateButtonMessage(array $template);

    public function generateEmptyMessage();

    public function generateFormMessage(array $template);

    public function generateImageMessage(array $template);

    public function generateListMessage(array $template);

    public function generateLongTextMessage(array $template);

    public function generateTextMessage(array $template);
}
