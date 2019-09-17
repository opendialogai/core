<?php

namespace OpenDialogAi\ResponseEngine\Message;

use OpenDialogAi\Core\ResponseEngine\Message\EmptyMessage;
use OpenDialogAi\Core\ResponseEngine\Message\FormMessage;
use OpenDialogAi\Core\ResponseEngine\Message\ImageMessage;
use OpenDialogAi\Core\ResponseEngine\Message\ListMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatLongTextMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatRichMessage;

/**
 * Message formatter interface.
 */
interface MessageFormatterInterface
{
    // TYPES
    public const ATTRIBUTE_MESSAGE = 'attribute-message';
    public const BUTTON_MESSAGE    = 'button-message';
    public const IMAGE_MESSAGE     = 'image-message';
    public const LIST_MESSAGE      = 'list-message';
    public const TEXT_MESSAGE      = 'text-message';
    public const RICH_MESSAGE      = 'rich-message';
    public const FORM_MESSAGE      = 'form-message';
    public const LONG_TEXT_MESSAGE = 'long-text-message';
    public const EMPTY_MESSAGE     = 'empty-message';

    // PROPERTIES
    public const BUTTONS                 = 'buttons';
    public const IMAGE                   = 'image';
    public const ITEMS                   = 'items';
    public const ELEMENTS                = 'elements';
    public const ELEMENT_TYPE            = 'element_type';
    public const AUTO_COMPLETE_SELECT    = 'auto_complete_select';
    public const SELECT                  = 'select';
    public const TEXTAREA                = 'textarea';
    public const TEXT                    = 'text';
    public const NUMBER                  = 'number';
    public const TITLE                   = 'title';
    public const SUBTITLE                = 'subtitle';
    public const CALLBACK                = 'callback';
    public const VALUE                   = 'value';
    public const LINK                    = 'link';
    public const URL                     = 'url';
    public const SRC                     = 'src';
    public const OPEN_NEW_TAB            = 'open-new-tab';
    public const LINK_NEW_TAB            = 'link_new_tab';
    public const CLICK_TO_CALL           = 'click_to_call';
    public const TAB_SWITCH              = 'tab_switch';
    public const PLACEHOLDER             = 'placeholder';
    public const INITIAL_TEXT            = 'initial_text';
    public const CONFIRMATION_TEXT       = 'confirmation_text';
    public const SUBMIT_TEXT             = 'submit_text';
    public const CHARACTER_LIMIT         = 'character_limit';
    public const AUTO_SUBMIT             = 'auto_submit';
    public const NAME                    = 'name';
    public const DISPLAY                 = 'display';
    public const REQUIRED                = 'required';
    public const OPTIONS                 = 'options';
    public const VIEW_TYPE               = 'view_type';
    public const DISABLE_TEXT            = 'disable_text';
    public const CLEAR_AFTER_INTERACTION = 'clear_after_interaction';

    public function getMessages(string $markup): array;

    public function generateButtonMessage(array $template): WebchatButtonMessage;

    public function generateEmptyMessage(): EmptyMessage;

    public function generateFormMessage(array $template): FormMessage;

    public function generateImageMessage(array $template): ImageMessage;

    public function generateListMessage(array $template): ListMessage;

    public function generateLongTextMessage(array $template): WebchatLongTextMessage;

    public function generateRichMessage(array $template): WebchatRichMessage;

    public function generateTextMessage(array $template): OpenDialogMessage;
}
