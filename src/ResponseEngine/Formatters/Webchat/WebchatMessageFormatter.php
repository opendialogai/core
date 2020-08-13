<?php

namespace OpenDialogAi\ResponseEngine\Formatters\Webchat;

use DOMDocument;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ResponseEngine\Formatters\BaseMessageFormatter;
use OpenDialogAi\ResponseEngine\Message\AutocompleteMessage;
use OpenDialogAi\ResponseEngine\Message\ButtonMessage;
use OpenDialogAi\ResponseEngine\Message\DatePickerMessage;
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
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\CallbackButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\ClickToCallButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\LinkButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\TabSwitchButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\TranscriptDownloadButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormAutoCompleteSelectElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormEmailElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormNumberElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormRadioElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormSelectElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormTextAreaElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormTextElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatAutocompleteMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatCtaMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatDatePickerMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatEmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatFormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatFullPageFormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatFullPageRichMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatHandToHumanMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatListMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatLongTextMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessages;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatMetaMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatRichMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatTextMessage;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use SimpleXMLElement;

/**
 * Webchat Message formatter.
 */
class WebchatMessageFormatter extends BaseMessageFormatter
{
    /** @var ResponseEngineService */
    private $responseEngineService;

    public static $name = 'formatter.core.webchat';

    /**
     * WebChatMessageFormatter constructor.
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->responseEngineService = app()->make(ResponseEngineServiceInterface::class);
    }

    /**
     * Convert the template to the appropriate message types.
     *
     * @param String $markup
     * @return OpenDialogMessages
     */
    public function getMessages(string $markup): OpenDialogMessages
    {
        $messages = [];
        try {
            $message = new SimpleXMLElement($markup);

            foreach ($message->children() as $item) {
                if ($item->getName() === self::ATTRIBUTE_MESSAGE) {
                    $attributeText = $this->getAttributeMessageText((string)$item);
                    return $this->getMessages($attributeText);
                }
                $messages[] = $this->parseMessage($item);
            }

            if (isset($message[self::DISABLE_TEXT])) {
                if ($message[self::DISABLE_TEXT] == '1' || $message[self::DISABLE_TEXT] == 'true') {
                    /** @var OpenDialogMessage $webChatMessage */
                    foreach ($messages as $webChatMessage) {
                        $webChatMessage->setDisableText(true);
                    }
                }
            }

            if (isset($message[self::HIDE_AVATAR])) {
                if ($message[self::HIDE_AVATAR] == '1' || $message[self::HIDE_AVATAR] == 'true') {
                    /** @var OpenDialogMessage $webChatMessage */
                    foreach ($messages as $webChatMessage) {
                        $webChatMessage->setHideAvatar(true);
                    }
                }
            }
        } catch (Exception $e) {
            Log::warning(sprintf('Message Builder error: %s', $e->getMessage()));
            return new WebChatMessages();
        }

        $messageWrapper = new WebChatMessages();
        foreach ($messages as $message) {
            $messageWrapper->addMessage($message);
        }

        return $messageWrapper;
    }

    /**
     * Parse XML markup and convert to the appropriate Message class.
     *
     * @param SimpleXMLElement $item
     * @return OpenDialogMessage
     */
    private function parseMessage(SimpleXMLElement $item)
    {
        switch ($item->getName()) {
            case self::BUTTON_MESSAGE:
                $template = $this->formatButtonTemplate($item);
                return $this->generateButtonMessage($template);
                break;
            case self::IMAGE_MESSAGE:
                $template = $this->formatImageTemplate($item);
                return $this->generateImageMessage($template);
                break;
            case self::LIST_MESSAGE:
                $template = $this->formatListTemplate($item);
                return $this->generateListMessage($template);
                break;
            case self::TEXT_MESSAGE:
                $text = $this->getMessageText($item);
                $template = [self::TEXT => $text];
                return $this->generateTextMessage($template);
                break;
            case self::RICH_MESSAGE:
                $template = $this->formatRichTemplate($item);
                return $this->generateRichMessage($template);
                break;
            case self::FULL_PAGE_RICH_MESSAGE:
                $template = $this->formatFullPageRichTemplate($item);
                return $this->generateFullPageRichMessage($template);
                break;
            case self::FORM_MESSAGE:
                $template = $this->formatFormTemplate($item);
                return $this->generateFormMessage($template);
                break;
            case self::FULL_PAGE_FORM_MESSAGE:
                $template = $this->formatFullPageFormTemplate($item);
                return $this->generateFullPageFormMessage($template);
                break;
            case self::LONG_TEXT_MESSAGE:
                $template = $this->formatLongTextTemplate($item);
                return $this->generateLongTextMessage($template);
                break;
            case self::HAND_TO_HUMAN_MESSAGE:
                $template = $this->formatHandToHumanTemplate($item);
                return $this->generateHandToHumanMessage($template);
                break;
            case self::CTA_MESSAGE:
                $text = $this->getMessageText($item);
                $template = [self::TEXT => $text];
                return $this->generateCtaMessage($template);
                break;
            case self::META_MESSAGE:
                $template = $this->formatMetaTemplate($item);
                return $this->generateMetaMessage($template);
                break;
            case self::AUTOCOMPLETE_MESSAGE:
                $template = $this->formatAutocompleteTemplate($item);
                return $this->generateAutocompleteMessage($template);
                break;
            case self::DATE_PICKER_MESSAGE:
                $template = $this->formatDatePickerMessage($item);
                return $this->generateDatePickerMessage($template);
                break;
            case self::EMPTY_MESSAGE:
                return new WebchatEmptyMessage();
                break;
            default:
                $template = [self::TEXT => 'Sorry, I did not understand this message type.'];
                return $this->generateTextMessage($template);
                break;
        }
    }

    /**
     * @param array $template
     * @return ButtonMessage
     */
    public function generateButtonMessage(array $template): ButtonMessage
    {
        $message = new WebchatButtonMessage();
        $message->setText($template[self::TEXT], [], true);
        $message->setExternal($template[self::EXTERNAL]);
        foreach ($template[self::BUTTONS] as $button) {
            $display = (isset($button[self::DISPLAY])) ? $button[self::DISPLAY] : true;
            $type = (isset($button[self::TYPE]) ? $button[self::TYPE] : '');

            if (isset($button[self::DOWNLOAD])) {
                $message->addButton(new TranscriptDownloadButton($button[self::TEXT], $display, $type));
            } elseif (isset($button[self::TAB_SWITCH])) {
                $message->addButton(new TabSwitchButton($button[self::TEXT], $display, $type));
            } elseif (isset($button[self::LINK])) {
                $message->addButton(new LinkButton(
                    $button[self::TEXT],
                    $button[self::LINK],
                    $button[self::LINK_NEW_TAB],
                    $display,
                    $type
                ));
            } elseif (isset($button[self::CLICK_TO_CALL])) {
                $message->addButton(new ClickToCallButton(
                    $button[self::TEXT],
                    $button[self::CLICK_TO_CALL],
                    $display,
                    $type
                ));
            } else {
                $message->addButton(new CallbackButton(
                    $button[self::TEXT],
                    $button[self::CALLBACK],
                    $button[self::VALUE],
                    $display,
                    $type
                ));
            }
        }

        $message->setClearAfterInteraction($template[self::CLEAR_AFTER_INTERACTION]);
        return $message;
    }

    /**
     * @return WebchatEmptyMessage
     */
    public function generateEmptyMessage(): EmptyMessage
    {
        $message = new WebchatEmptyMessage();
        return $message;
    }

    /**
     * @param array $template
     * @return FormMessage
     */
    public function generateFormMessage(array $template): FormMessage
    {
        $message = (new WebchatFormMessage())
            ->setText($template[self::TEXT])
            ->setCallbackId($template[self::CALLBACK])
            ->setAutoSubmit($template[self::AUTO_SUBMIT]);

        if ($template[self::SUBMIT_TEXT]) {
            $message->setSubmitText($template[self::SUBMIT_TEXT]);
        }

        if ($template[self::CANCEL_TEXT]) {
            $message->setCancelText($template[self::CANCEL_TEXT]);
        }

        if ($template[self::CANCEL_CALLBACK]) {
            $message->setCancelCallback($template[self::CANCEL_CALLBACK]);
        }

        foreach ($template[self::ELEMENTS] as $el) {
            $name = $el[self::NAME];
            $display = $el[self::DISPLAY];
            $required = $el[self::REQUIRED];
            $defaultValue = $el[self::DEFAULT_VALUE];

            if ($el[self::ELEMENT_TYPE] == self::TEXTAREA) {
                $element = new FormTextAreaElement($name, $display, $required, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::TEXT) {
                $element = new FormTextElement($name, $display, $required, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::NUMBER) {
                $element = new FormNumberElement($name, $display, $required, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::SELECT) {
                $options = $el[self::OPTIONS];
                $element = new FormSelectElement($name, $display, $required, $options, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::AUTO_COMPLETE_SELECT) {
                $options = $el[self::OPTIONS];
                $element = new FormAutoCompleteSelectElement($name, $display, $required, $options, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::RADIO) {
                $options = $el[self::OPTIONS];
                $element = new FormRadioElement($name, $display, $required, $options, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::EMAIL) {
                $element = new FormEmailElement($name, $display, $required, $defaultValue);
            }
            $message->addElement($element);
        }

        return $message;
    }

    /**
     * @param array $template
     * @return FullPageFormMessage
     */
    public function generateFullPageFormMessage(array $template): FullPageFormMessage
    {
        $message = (new WebchatFullPageFormMessage())
            ->setText($template[self::TEXT])
            ->setCallbackId($template[self::CALLBACK])
            ->setAutoSubmit($template[self::AUTO_SUBMIT]);

        if ($template[self::SUBMIT_TEXT]) {
            $message->setSubmitText($template[self::SUBMIT_TEXT]);
        }

        if ($template[self::CANCEL_TEXT]) {
            $message->setCancelText($template[self::CANCEL_TEXT]);
        }

        if ($template[self::CANCEL_CALLBACK]) {
            $message->setCancelCallback($template[self::CANCEL_CALLBACK]);
        }

        foreach ($template[self::ELEMENTS] as $el) {
            $name = $el[self::NAME];
            $display = $el[self::DISPLAY];
            $required = $el[self::REQUIRED];
            $defaultValue = $el[self::DEFAULT_VALUE];

            if ($el[self::ELEMENT_TYPE] == self::TEXTAREA) {
                $element = new FormTextAreaElement($name, $display, $required, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::TEXT) {
                $element = new FormTextElement($name, $display, $required, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::NUMBER) {
                $element = new FormNumberElement($name, $display, $required, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::SELECT) {
                $options = $el[self::OPTIONS];
                $element = new FormSelectElement($name, $display, $required, $options, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::AUTO_COMPLETE_SELECT) {
                $options = $el[self::OPTIONS];
                $element = new FormAutoCompleteSelectElement($name, $display, $required, $options, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::RADIO) {
                $options = $el[self::OPTIONS];
                $element = new FormRadioElement($name, $display, $required, $options, $defaultValue);
            } elseif ($el[self::ELEMENT_TYPE] == self::EMAIL) {
                $element = new FormEmailElement($name, $display, $required, $defaultValue);
            }
            $message->addElement($element);
        }

        return $message;
    }

    /**
     * @param array $template
     * @return ImageMessage
     */
    public function generateImageMessage(array $template): ImageMessage
    {
        $message = (new WebchatImageMessage())
            ->setImgSrc($template[self::SRC])
            ->setImgLink($template[self::LINK])
            ->setLinkNewTab($template[self::LINK_NEW_TAB]);

        return $message;
    }

    /**
     * @param array $template
     * @return RichMessage
     */
    public function generateRichMessage(array $template): RichMessage
    {
        $message = (new WebchatRichMessage())
            ->setTitle($template[self::TITLE])
            ->setSubTitle($template[self::SUBTITLE])
            ->setText($template[self::TEXT])
            ->setCallback($template[self::CALLBACK])
            ->setCallbackValue($template[self::CALLBACK_VALUE])
            ->setLink($template[self::LINK]);

        if (isset($template[self::IMAGE])) {
            $message->setImageSrc($template[self::IMAGE][self::SRC]);
            $message->setImageLink($template[self::IMAGE][self::URL]);
            $message->setImageLinkNewTab($template[self::IMAGE][self::LINK_NEW_TAB]);
        }

        if (isset($template[self::BUTTONS])) {
            foreach ($template[self::BUTTONS] as $button) {
                if (isset($button[self::DOWNLOAD])) {
                    $message->addButton(new TranscriptDownloadButton($button[self::TEXT]));
                } elseif (isset($button[self::TAB_SWITCH])) {
                    $message->addButton(new TabSwitchButton($button[self::TEXT]));
                } elseif (isset($button[self::LINK])) {
                    $linkNewTab = $button[self::LINK_NEW_TAB];
                    $message->addButton(new LinkButton($button[self::TEXT], $button[self::LINK], $linkNewTab));
                } else {
                    $message->addButton(
                        new CallbackButton($button[self::TEXT], $button[self::CALLBACK], $button[self::VALUE])
                    );
                }
            }
        }

        return $message;
    }

    /**
     * @param array $template
     * @return FullPageRichMessage
     */
    public function generateFullPageRichMessage(array $template): FullPageRichMessage
    {
        $message = (new WebchatFullPageRichMessage())
            ->setTitle($template[self::TITLE])
            ->setSubTitle($template[self::SUBTITLE])
            ->setText($template[self::TEXT]);

        if (isset($template[self::IMAGE])) {
            $message->setImageSrc($template[self::IMAGE][self::SRC]);
            $message->setImageLink($template[self::IMAGE][self::URL]);
            $message->setImageLinkNewTab($template[self::IMAGE][self::LINK_NEW_TAB]);
        }

        if (isset($template[self::BUTTONS])) {
            foreach ($template[self::BUTTONS] as $button) {
                $display = (isset($button[self::DISPLAY])) ? $button[self::DISPLAY] : true;
                $type = (isset($button[self::TYPE]) ? $button[self::TYPE] : '');

                if (isset($button[self::DOWNLOAD])) {
                    $message->addButton(new TranscriptDownloadButton($button[self::TEXT], $display, $type));
                } elseif (isset($button[self::TAB_SWITCH])) {
                    $message->addButton(new TabSwitchButton($button[self::TEXT], $display, $type));
                } elseif (isset($button[self::LINK])) {
                    $linkNewTab = $button[self::LINK_NEW_TAB];
                    $message->addButton(new LinkButton($button[self::TEXT], $button[self::LINK], $linkNewTab, $display, $type));
                } else {
                    $message->addButton(
                        new CallbackButton($button[self::TEXT], $button[self::CALLBACK], $button[self::VALUE], $display, $type)
                    );
                }
            }
        }

        return $message;
    }

    /**
     * @param array $template
     * @return ListMessage
     */
    public function generateListMessage(array $template): ListMessage
    {
        $message = (new WebchatListMessage())
            ->addItems($template[self::ITEMS])
            ->setViewType($template[self::VIEW_TYPE])
            ->setTitle($template[self::TITLE]);

        return $message;
    }

    /**
     * @param array $template
     * @return LongTextMessage
     */
    public function generateLongTextMessage(array $template): LongTextMessage
    {
        $message = (new WebchatLongTextMessage())
            ->setSubmitText($template[self::SUBMIT_TEXT])
            ->setCharacterLimit($template[self::CHARACTER_LIMIT])
            ->setCallbackId($template[self::CALLBACK])
            ->setInitialText($template[self::INITIAL_TEXT])
            ->setPlaceholder($template[self::PLACEHOLDER])
            ->setConfirmationText($template[self::CONFIRMATION_TEXT]);

        return $message;
    }

    /**
     * @param array $template
     * @return HandToHumanMessage
     */
    public function generateHandToHumanMessage(array $template): HandToHumanMessage
    {
        $message = (new WebchatHandToHumanMessage())->setElements($template[self::ELEMENTS]);
        return $message;
    }

    /**
     * @param array $template
     * @return OpenDialogMessage
     */
    public function generateTextMessage(array $template): OpenDialogMessage
    {
        $message = (new WebchatTextMessage())->setText($template[self::TEXT], [], true);
        return $message;
    }

    /**
     * @param array $template
     * @return MetaMessage
     */
    public function generateMetaMessage(array $template): MetaMessage
    {
        $message = (new WebchatMetaMessage())->setElements($template[self::ELEMENTS]);
        return $message;
    }

    /**
     * @param array $template
     * @return AutocompleteMessage
     */
    public function generateAutocompleteMessage(array $template): AutocompleteMessage
    {
        $message = (new WebchatAutocompleteMessage())
            ->setTitle($template[self::TITLE])
            ->setEndpointUrl($template[self::ENDPOINT_URL])
            ->setEndpointParams($template[self::ENDPOINT_PARAMS])
            ->setCallback($template[self::CALLBACK])
            ->setSubmitText($template[self::SUBMIT_TEXT])
            ->setQueryParamName($template[self::QUERY_PARAM_NAME])
            ->setPlaceholder($template[self::PLACEHOLDER])
            ->setAttributeName($template[self::ATTRIBUTE_NAME]);
        return $message;
    }

    public function generateDatePickerMessage(array $template): DatePickerMessage
    {
         return (new WebchatDatePickerMessage())
             ->setSubmitText($template[self::SUBMIT_TEXT])
             ->setText($template[self::TEXT])
             ->setCallback($template[self::CALLBACK])
             ->setDayRequired($template[self::DAY_REQUIRED])
             ->setMonthRequired($template[self::MONTH_REQUIRED])
             ->setYearRequired($template[self::YEAR_REQUIRED])
             ->setMaxDate($template[self::MAX_DATE])
             ->setMinDate($template[self::MIN_DATE]);
    }

    /**
     * Resolves the attribute by name to get the value for the attribute message, then resolves any attributes
     * in the resulting text
     *
     * @param string $attributeName
     * @return string
     */
    protected function getAttributeMessageText($attributeName): string
    {
        $parsedAttributeName = ContextParser::parseAttributeName($attributeName);

        $attributeValue = ContextService::getAttributeValue($parsedAttributeName->attributeId, $parsedAttributeName->contextId);

        return $this->responseEngineService->fillAttributes($attributeValue);
    }

    /**
     * @param SimpleXMLElement $element
     * @return string
     */
    protected function getMessageText(SimpleXMLElement $element): string
    {
        $dom = new DOMDocument();
        $dom->loadXML($element->asXml());

        $text = '';
        foreach ($dom->childNodes as $node) {
            foreach ($node->childNodes as $item) {
                if ($item->nodeType === XML_TEXT_NODE) {
                    if (!empty(trim($item->textContent))) {
                        $text .= ' ' . trim($item->textContent);
                    }
                } elseif ($item->nodeType === XML_ELEMENT_NODE) {
                    if ($item->nodeName === self::LINK) {
                        $openNewTab = $this->convertToBoolean((string)$item->getAttribute('new_tab'));

                        $link = [
                            self::OPEN_NEW_TAB => $openNewTab,
                            self::TEXT => '',
                            self::URL => '',
                        ];

                        foreach ($item->childNodes as $t) {
                            $link[$t->nodeName] = trim($t->nodeValue);
                        }

                        if ($link[self::URL]) {
                            $text .= ' ' . $this->generateLinkHtml(
                                $link[self::URL],
                                $link[self::TEXT],
                                $link[self::OPEN_NEW_TAB]
                            );
                        } else {
                            Log::debug('Not adding link to message text, url is empty');
                        }
                    }
                }
            }
        }

        return trim($text);
    }

    /**
     * Generates the appropriate link based on the $openNewTab property
     *
     * @param string $url
     * @param string $text
     * @param bool $openNewTab
     * @return string
     */
    protected function generateLinkHtml($url, $text, $openNewTab)
    {
        if ($openNewTab) {
            return '<a class="linkified" target="_blank" href="' . $url . '">' . $text . '</a>';
        }

        return '<a class="linkified" target="_parent" href="' . $url . '">' . $text . '</a>';
    }

    /**
     * Formats the template for button message based
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatButtonTemplate(SimpleXMLElement $item): array
    {
        $clearAfterInteraction = false;
        if (isset($item[self::CLEAR_AFTER_INTERACTION])) {
            $clearAfterInteraction = $item[self::CLEAR_AFTER_INTERACTION] == '1' ? true : false;
        }

        $external = false;
        if (isset($item->external)) {
            $external = $item->external == 'true' ? true : false;
        }

        $template = [
            self::TEXT => $this->getMessageText($item->text),
            self::EXTERNAL => $external,
            self::CLEAR_AFTER_INTERACTION => $clearAfterInteraction
        ];

        foreach ($item->button as $button) {
            $attributes = $button->attributes();

            $display = ((string)$button->display) ? $this->convertToBoolean((string)$button->display) : true;
            $type = $attributes[self::TYPE] ?: "";

            if (isset($button->download)) {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::DOWNLOAD => true,
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            } elseif (isset($button->tab_switch)) {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::TAB_SWITCH => true,
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            } elseif (isset($button->link)) {
                $buttonLinkNewTab = $this->convertToBoolean((string)$button->link['new_tab']);

                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::LINK => trim((string)$button->link),
                    self::LINK_NEW_TAB => $buttonLinkNewTab,
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            } elseif (isset($button->click_to_call)) {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::CLICK_TO_CALL => trim((string)$button->click_to_call),
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            } else {
                $dom = new DOMDocument();
                $dom->loadXML($button->text->asXml());

                $buttonText = '';
                foreach ($dom->childNodes as $node) {
                    foreach ($node->childNodes as $item) {
                        if ($item->nodeType === XML_TEXT_NODE) {
                            if (!empty(trim($item->textContent))) {
                                $buttonText .= ' ' . trim($item->textContent);
                            }
                        } elseif ($item->nodeType === XML_ELEMENT_NODE) {
                            if (!empty(trim($item->textContent))) {
                                $buttonText .= ' ';
                                if ($item->nodeName === 'b') {
                                    $buttonText .= sprintf(
                                        '<strong>%s</strong>',
                                        trim($item->textContent)
                                    );
                                } elseif ($item->nodeName === 'i') {
                                    $buttonText .= sprintf(
                                        '<em>%s</em>',
                                        trim($item->textContent)
                                    );
                                } elseif ($item->nodeName === 'u') {
                                    $buttonText .= sprintf(
                                        '<u>%s</u>',
                                        trim($item->textContent)
                                    );
                                }
                            }
                        }
                    }
                }

                $template[self::BUTTONS][] = [
                    self::CALLBACK => trim((string)$button->callback),
                    self::TEXT => trim($buttonText),
                    self::VALUE => trim((string)$button->value),
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            }
        }
        return $template;
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatHandToHumanTemplate(SimpleXMLElement $item): array
    {
        $elements = [];
        foreach ($item->data as $data) {
            $elements[(string)$data['name']] = (string)$data;
        }

        $template = [
            self::ELEMENTS => $elements,
        ];
        return $template;
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatImageTemplate(SimpleXMLElement $item): array
    {
        $linkNewTab = false;
        if (isset($item[self::LINK_NEW_TAB])) {
            $linkNewTab = $item[self::LINK_NEW_TAB] == '1' ? true : false;
        }

        $template = [
            self::LINK_NEW_TAB => $linkNewTab,
            self::LINK => trim((string)$item->link),
            self::SRC => trim((string)$item->src),
        ];
        return $template;
    }

    private function formatRichTemplate(SimpleXMLElement $item): array
    {
        $template = [
            self::TITLE => trim((string)$item->title),
            self::SUBTITLE => trim((string)$item->subtitle),
            self::TEXT => trim((string)$item->text),
            self::CALLBACK => trim((string)$item->callback),
            self::CALLBACK_VALUE => trim((string)$item->callback_value),
            self::LINK => trim((string)$item->link),
        ];

        if ($item->image->count()) {
            $linkNewTab = $this->convertToBoolean((string)$item->image->url['new_tab']);

            $template[self::IMAGE] = [
                self::SRC => trim((string)$item->image->src),
                self::URL => trim((string)$item->image->url),
                self::LINK_NEW_TAB => $linkNewTab,
            ];
        }

        foreach ($item->button as $button) {
            $attributes = $button->attributes();

            $display = ((string)$button->display) ? $this->convertToBoolean((string)$button->display) : true;
            $type = $attributes[self::TYPE] ?: "";

            if (isset($button->download)) {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::DOWNLOAD => true,
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            } elseif (isset($button->tab_switch)) {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::TAB_SWITCH => true,
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            } elseif (isset($button->link)) {
                $buttonLinkNewTab = $this->convertToBoolean((string)$button->link['new_tab']);

                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::LINK => trim((string)$button->link),
                    self::LINK_NEW_TAB => $buttonLinkNewTab,
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            } else {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::CALLBACK => trim((string)$button->callback),
                    self::VALUE => trim((string)$button->value),
                    self::DISPLAY => $display,
                    self::TYPE => $type,
                ];
            }
        }

        return $template;
    }

    private function formatFullPageRichTemplate(SimpleXMLElement $item): array
    {
        return $this->formatRichTemplate($item);
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatListTemplate(SimpleXMLElement $item): array
    {
        $items = [];

        $viewType = ($item['view-type']) ? (string)$item['view-type'] : 'horizontal';

        foreach ($item->item as $i => $itemMessage) {
            $items[] = $this->parseMessage($itemMessage->children()[0]);
        }

        $template = [
            self::TITLE => trim((string)$item->title),
            self::ITEMS => $items,
            self::VIEW_TYPE => $viewType,
        ];
        return $template;
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatFormTemplate(SimpleXMLElement $item): array
    {
        $elements = [];

        foreach ($item->element as $element) {
            $required = $this->convertToBoolean((string)$element->required) ? true : false;

            $el = [
                self::ELEMENT_TYPE => trim((string)$element->element_type),
                self::NAME => trim((string)$element->name),
                self::DISPLAY => trim((string)$element->display),
                self::DEFAULT_VALUE => trim((string)$element->default_value),
                self::REQUIRED => $required,
            ];

            if ($el[self::ELEMENT_TYPE] == self::SELECT || $el[self::ELEMENT_TYPE] == self::AUTO_COMPLETE_SELECT) {
                $options = [];

                foreach ($element->options->children() as $option) {
                    $options[trim((string)$option->key)] = trim((string)$option->value);
                }
                $el[self::OPTIONS] = $options;
            }

            if ($el[self::ELEMENT_TYPE] == self::RADIO) {
                $options = [];

                foreach ($element->options->children() as $option) {
                    $options[trim((string)$option->key)] = trim((string)$option->value);
                }
                $el[self::OPTIONS] = $options;
            }

            $elements[] = $el;
        }

        $autoSubmit = $this->convertToBoolean((string)$item->auto_submit);

        return [
            self::TEXT => trim((string)$item->text),
            self::SUBMIT_TEXT => trim((string)$item->submit_text),
            self::CALLBACK => trim((string)$item->callback),
            self::AUTO_SUBMIT => $autoSubmit,
            self::ELEMENTS => $elements,
            self::CANCEL_CALLBACK => trim((string)$item->cancel_callback ?? null),
            self::CANCEL_TEXT => trim((string)$item->cancel_text ?? null),
        ];
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatFullPageFormTemplate(SimpleXMLElement $item): array
    {
        return $this->formatFormTemplate($item);
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatLongTextTemplate(SimpleXMLElement $item): array
    {
        $template = [
            self::SUBMIT_TEXT => trim((string)$item->submit_text),
            self::CALLBACK => trim((string)$item->callback),
            self::INITIAL_TEXT => trim((string)$item->initial_text),
            self::PLACEHOLDER => trim((string)$item->placeholder),
            self::CONFIRMATION_TEXT => trim((string)$item->confirmation_text),
            self::CHARACTER_LIMIT => trim((string)$item->character_limit),
        ];
        return $template;
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatMetaTemplate(SimpleXMLElement $item): array
    {
        $elements = [];
        foreach ($item->data as $data) {
            $elements[(string)$data['name']] = (string)$data;
        }

        $template = [
            self::ELEMENTS => $elements,
        ];
        return $template;
    }

    /**
     * Formats the XML item into the required template format
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function formatAutocompleteTemplate(SimpleXMLElement $item): array
    {
        $endpointParams = [];

        if (isset($item->{'options-endpoint'}->params)) {
            foreach ($item->{'options-endpoint'}->params->param as $param) {
                $endpointParams[] = [
                    self::NAME => (string)$param['name'],
                    self::VALUE => (string)$param['value'],
                ];
            }
        }

        $template = [
            self::TITLE => trim((string)$item->title),
            self::ENDPOINT_URL => (string)$item->{'options-endpoint'}->url,
            self::SUBMIT_TEXT => (string)$item->submit_text,
            self::CALLBACK => (string)$item->callback,
            self::PLACEHOLDER => (string)$item->placeholder,
            self::ATTRIBUTE_NAME => (string)$item->attribute_name,
            self::ENDPOINT_PARAMS => $endpointParams,
            self::QUERY_PARAM_NAME => (string)$item->{'options-endpoint'}->{'query-param-name'},
        ];
        return $template;
    }

    public function formatDatePickerMessage(SimpleXMLElement $item): array
    {
        return [
            self::TEXT => trim((string)$item->text),
            self::SUBMIT_TEXT => trim((string)$item->submit_text),
            self::CALLBACK => trim((string)$item->callback),
            self::MIN_DATE => (string)$item->min_date ?? null,
            self::MAX_DATE => (string)$item->max_date ?? null,
            self::DAY_REQUIRED => (string)$item->day_required ?? true,
            self::MONTH_REQUIRED => (string)$item->month_required ?? true,
            self::YEAR_REQUIRED => (string)$item->year_required ?? true,
        ];
    }

    /**
     * @param array $template
     * @return OpenDialogMessage
     */
    public function generateCtaMessage(array $template): OpenDialogMessage
    {
        return (new WebchatCtaMessage())->setText($template[self::TEXT], [], true);
    }

    /**
     * @param string $value
     * @return bool
     */
    private function convertToBoolean(string $value): bool
    {
        if ($value === '1' || $value === 'true') {
            return true;
        }
        return false;
    }
}
