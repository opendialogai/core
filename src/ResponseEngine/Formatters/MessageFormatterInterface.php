<?php

namespace OpenDialogAi\ResponseEngine\Formatters;

use OpenDialogAi\Core\Exceptions\NameNotSetException;
use OpenDialogAi\ResponseEngine\Message\AutocompleteMessage;
use OpenDialogAi\ResponseEngine\Message\ButtonMessage;
use OpenDialogAi\ResponseEngine\Message\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\FormMessage;
use OpenDialogAi\ResponseEngine\Message\FullPageFormMessage;
use OpenDialogAi\ResponseEngine\Message\FullPageRichMessage;
use OpenDialogAi\ResponseEngine\Message\HandToHumanMessage;
use OpenDialogAi\ResponseEngine\Message\ImageMessage;
use OpenDialogAi\ResponseEngine\Message\ListMessage;
use OpenDialogAi\ResponseEngine\Message\LongTextMessage;
use OpenDialogAi\ResponseEngine\Message\MetaMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessages;
use OpenDialogAi\ResponseEngine\Message\RichMessage;

/**
 * Message formatter interface.
 */
interface MessageFormatterInterface
{
    // TYPES
    public const ATTRIBUTE_MESSAGE      = 'attribute-message';
    public const BUTTON_MESSAGE         = 'button-message';
    public const HAND_TO_HUMAN_MESSAGE  = 'hand-to-human-message';
    public const IMAGE_MESSAGE          = 'image-message';
    public const LIST_MESSAGE           = 'list-message';
    public const TEXT_MESSAGE           = 'text-message';
    public const RICH_MESSAGE           = 'rich-message';
    public const FORM_MESSAGE           = 'form-message';
    public const FULL_PAGE_FORM_MESSAGE = 'fp-form-message';
    public const FULL_PAGE_RICH_MESSAGE = 'fp-rich-message';
    public const LONG_TEXT_MESSAGE      = 'long-text-message';
    public const EMPTY_MESSAGE          = 'empty-message';
    public const CTA_MESSAGE            = 'cta-message';
    public const META_MESSAGE           = 'meta-message';
    public const AUTOCOMPLETE_MESSAGE   = 'autocomplete-message';

    // PROPERTIES
    public const BUTTONS                 = 'buttons';
    public const IMAGE                   = 'image';
    public const ITEMS                   = 'items';
    public const ELEMENTS                = 'elements';
    public const ELEMENT_TYPE            = 'element_type';
    public const AUTO_COMPLETE_SELECT    = 'auto_complete_select';
    public const SELECT                  = 'select';
    public const EMAIL                   = 'email';
    public const RADIO                   = 'radio';
    public const TEXTAREA                = 'textarea';
    public const TEXT                    = 'text';
    public const NUMBER                  = 'number';
    public const TITLE                   = 'title';
    public const SUBTITLE                = 'subtitle';
    public const CALLBACK                = 'callback';
    public const CALLBACK_VALUE          = 'callback_value';
    public const VALUE                   = 'value';
    public const LINK                    = 'link';
    public const URL                     = 'url';
    public const SRC                     = 'src';
    public const OPEN_NEW_TAB            = 'open-new-tab';
    public const LINK_NEW_TAB            = 'link_new_tab';
    public const CLICK_TO_CALL           = 'click_to_call';
    public const TAB_SWITCH              = 'tab_switch';
    public const DOWNLOAD                = 'download';
    public const EXTERNAL                = 'external';
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
    public const DEFAULT_VALUE           = 'default_value';
    public const VIEW_TYPE               = 'view_type';
    public const DISABLE_TEXT            = 'disable_text';
    public const HIDE_AVATAR             = 'hide_avatar';
    public const CLEAR_AFTER_INTERACTION = 'clear_after_interaction';
    public const CANCEL_CALLBACK         = 'cancel_callback';
    public const CANCEL_TEXT             = 'cancel_text';
    public const TYPE                    = 'type';
    public const ENDPOINT_URL            = 'endpoint_url';
    public const ENDPOINT_PARAMS         = 'endpoint_params';
    public const QUERY_PARAM_NAME        = 'query_param_name';

    public function getMessages(string $markup): OpenDialogMessages;

    public function generateAutocompleteMessage(array $template): AutocompleteMessage;

    public function generateButtonMessage(array $template): ButtonMessage;

    public function generateEmptyMessage(): EmptyMessage;

    public function generateFormMessage(array $template): FormMessage;

    public function generateFullPageFormMessage(array $template): FullPageFormMessage;

    public function generateImageMessage(array $template): ImageMessage;

    public function generateListMessage(array $template): ListMessage;

    public function generateMetaMessage(array $template): MetaMessage;

    public function generateLongTextMessage(array $template): LongTextMessage;

    public function generateRichMessage(array $template): RichMessage;

    public function generateFullPageRichMessage(array $template): FullPageRichMessage;

    public function generateTextMessage(array $template): OpenDialogMessage;

    public function generateHandToHumanMessage(array $template): HandToHumanMessage;

    /**
     * @return string
     * @throws NameNotSetException
     */
    public static function getName(): string;
}
