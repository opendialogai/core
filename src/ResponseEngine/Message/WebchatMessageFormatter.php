<?php

namespace OpenDialogAi\ResponseEngine\Message;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\WebchatCallbackButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\WebchatLinkButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\WebchatTabSwitchButton;
use OpenDialogAi\ResponseEngine\Message\Webchat\EmptyMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormAutoCompleteSelectElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormNumberElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormSelectElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormTextAreaElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\WebChatFormTextElement;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatFormMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatImageMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatListMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatLongTextMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatRichMessage;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use SimpleXMLElement;

/**
 * Webchat Message formatter.
 */
class WebChatMessageFormatter implements MessageFormatterInterface
{

    /** @var ResponseEngineService */
    private $responseEngineService;

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
     * @return WebChatMessage[]
     */
    public function getMessages(string $markup): array
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
                    foreach ($messages as $webChatMessage) {
                        $webChatMessage->setDisableText(true);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning(sprintf('Message Builder error: %s', $e->getMessage()));
            return [];
        }

        return $messages;
    }

    /**
     * Parse XML markup and convert to the appropriate Message class.
     *
     * @param SimpleXMLElement $item
     * @return WebChatMessage
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
            case self::FORM_MESSAGE:
                $template = $this->formatFormTemplate($item);
                return $this->generateFormMessage($template);
                break;
            case self::LONG_TEXT_MESSAGE:
                $template = $this->formatLongTextTemplate($item);
                return $this->generateLongTextMessage($template);
                break;
            case self::EMPTY_MESSAGE:
                return new EmptyMessage();
                break;
            default:
                $template = [self::TEXT => 'Sorry, I did not understand this message type.'];
                return $this->generateTextMessage($template);
                break;
        }
    }

    /**
     * @param array $template
     * @return WebChatButtonMessage
     */
    public function generateButtonMessage(array $template)
    {
        $message = new WebChatButtonMessage();
        $message->setText($template[self::TEXT]);
        foreach ($template[self::BUTTONS] as $button) {
            if (isset($button[self::TAB_SWITCH])) {
                $message->addButton(new WebchatTabSwitchButton($button[self::TEXT]));
            } elseif (isset($button[self::LINK])) {
                $message->addButton(new WebchatLinkButton($button[self::TEXT], $button[self::LINK], $button[self::LINK_NEW_TAB]));
            } else {
                $message->addButton(
                    new WebchatCallbackButton($button[self::TEXT], $button[self::CALLBACK], $button[self::VALUE])
                );
            }
        }

        $message->setClearAfterInteraction($template[self::CLEAR_AFTER_INTERACTION]);
        return $message;
    }

    /**
     * @return EmptyMessage
     */
    public function generateEmptyMessage()
    {
        $message = new EmptyMessage();
        return $message;
    }

    /**
     * @param array $template
     * @return WebChatFormMessage
     */
    public function generateFormMessage(array $template)
    {
        $message = (new WebChatFormMessage())
            ->setText($template[self::TEXT])
            ->setCallbackId($template[self::CALLBACK])
            ->setAutoSubmit($template[self::AUTO_SUBMIT]);

        if ($template[self::SUBMIT_TEXT]) {
            $message->setSubmitText($template[self::SUBMIT_TEXT]);
        }

        foreach ($template[self::ELEMENTS] as $el) {
            $name = $el[self::NAME];
            $display = $el[self::DISPLAY];
            $required = $el[self::REQUIRED];

            if ($el[self::ELEMENT_TYPE] == self::TEXTAREA) {
                $element = new WebChatFormTextAreaElement($name, $display, $required);
            } elseif ($el[self::ELEMENT_TYPE] == self::TEXT) {
                $element = new WebChatFormTextElement($name, $display, $required);
            } elseif ($el[self::ELEMENT_TYPE] == self::NUMBER) {
                $element = new WebChatFormNumberElement($name, $display, $required);
            } elseif ($el[self::ELEMENT_TYPE] == self::SELECT) {
                $options = $el[self::OPTIONS];
                $element = new WebChatFormSelectElement($name, $display, $required, $options);
            } elseif ($el[self::ELEMENT_TYPE] == self::AUTO_COMPLETE_SELECT) {
                $options = $el[self::OPTIONS];
                $element = new WebChatFormAutoCompleteSelectElement($name, $display, $required, $options);
            }
            $message->addElement($element);
        }

        return $message;
    }

    /**
     * @param array $template
     * @return WebChatImageMessage
     */
    public function generateImageMessage(array $template)
    {
        $message = (new WebChatImageMessage())
            ->setImgSrc($template[self::SRC])
            ->setImgLink($template[self::LINK])
            ->setLinkNewTab($template[self::LINK_NEW_TAB]);

        return $message;
    }

    public function generateRichMessage(array $template)
    {
        $message = (new WebChatRichMessage())
            ->setTitle($template[self::TITLE])
            ->setSubTitle($template[self::SUBTITLE])
            ->setText($template[self::TEXT])
            ->setImageSrc($template[self::IMAGE][self::SRC])
            ->setImageLink($template[self::IMAGE][self::URL])
            ->setImageLinkNewTab($template[self::IMAGE][self::LINK_NEW_TAB]);

        if (isset($template[self::BUTTONS])) {
            foreach ($template[self::BUTTONS] as $button) {
                if (isset($button[self::TAB_SWITCH])) {
                    $message->addButton(new WebchatTabSwitchButton($button[self::TEXT]));
                } elseif (isset($button[self::LINK])) {
                    $linkNewTab = $button[self::LINK_NEW_TAB];
                    $message->addButton(new WebchatLinkButton($button[self::TEXT], $button[self::LINK], $linkNewTab));
                } else {
                    $message->addButton(
                        new WebchatCallbackButton($button[self::TEXT], $button[self::CALLBACK], $button[self::VALUE])
                    );
                }
            }
        }

        return $message;
    }

    public function generateListMessage(array $template)
    {
        $message = (new WebChatListMessage())
            ->addItems($template[self::ITEMS])
            ->setViewType($template[self::VIEW_TYPE]);

        return $message;
    }

    public function generateLongTextMessage(array $template)
    {
        $message = (new WebChatLongTextMessage())
            ->setSubmitText($template[self::SUBMIT_TEXT])
            ->setCharacterLimit($template[self::CHARACTER_LIMIT])
            ->setCallbackId($template[self::CALLBACK])
            ->setInitialText($template[self::INITIAL_TEXT])
            ->setPlaceholder($template[self::PLACEHOLDER])
            ->setConfirmationText($template[self::CONFIRMATION_TEXT]);

        return $message;
    }

    public function generateTextMessage(array $template)
    {
        $message = (new WebChatMessage())->setText($template[self::TEXT], [], true);
        return $message;
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
        [$contextId, $attributeId] = ContextParser::determineContextAndAttributeId($attributeName);
        $attributeValue = ContextService::getAttributeValue($attributeId, $contextId);

        return $this->responseEngineService->fillAttributes($attributeValue);
    }

    /**
     * @param SimpleXMLElement $element
     * @return string
     */
    protected function getMessageText(SimpleXMLElement $element): string
    {
        $dom = new \DOMDocument;
        $dom->loadXML($element->asXml());

        $text = '';
        foreach ($dom->childNodes as $node) {
            foreach ($node->childNodes as $item) {
                if ($item->nodeType === XML_TEXT_NODE) {
                    $text .= trim($item->textContent);
                } elseif ($item->nodeType === XML_ELEMENT_NODE) {
                    if ($item->nodeName === self::LINK) {
                        $openNewTab = ($item->getAttribute('new_tab')) ? true : false;

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

        return $text;
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
            return '<a target="_blank" href="' . $url . '">' . $text . '</a>';
        }

        return '<a href="' . $url . '">' . $text . '</a>';
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

        $template = [
            self::TEXT => trim((string)$item->text),
            self::CLEAR_AFTER_INTERACTION => $clearAfterInteraction
        ];

        foreach ($item->button as $button) {
            if (isset($button->tab_switch)) {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::TAB_SWITCH => true,
                ];
            } elseif (isset($button->link)) {
                $buttonLinkNewTab = ($button->link['new_tab']) ? true : false;

                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::LINK => trim((string)$button->link),
                    self::LINK_NEW_TAB => $buttonLinkNewTab,
                ];
            } else {
                $template[self::BUTTONS][] = [
                    self::CALLBACK => trim((string)$button->callback),
                    self::TEXT => trim((string)$button->text),
                    self::VALUE => trim((string)$button->value),
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
        $linkNewTab = ($item->image->url['new_tab']) ? true : false;

        $template = [
            self::TITLE => trim((string)$item->title),
            self::SUBTITLE => trim((string)$item->subtitle),
            self::TEXT => trim((string)$item->text),
            self::IMAGE => [
                self::SRC => trim((string)$item->image->src),
                self::URL => trim((string)$item->image->url),
                self::LINK_NEW_TAB => $linkNewTab,
            ],
        ];

        foreach ($item->button as $button) {
            if (isset($button->tab_switch)) {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::TAB_SWITCH => true,
                ];
            } elseif (isset($button->link)) {
                $buttonLinkNewTab = ($button->link['new_tab']) ? true : false;

                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::LINK => trim((string)$button->link),
                    self::LINK_NEW_TAB => $buttonLinkNewTab,
                ];
            } else {
                $template[self::BUTTONS][] = [
                    self::TEXT => trim((string)$button->text),
                    self::CALLBACK => trim((string)$button->callback),
                    self::VALUE => trim((string)$button->value),
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
    private function formatListTemplate(SimpleXMLElement $item): array
    {
        $items = [];

        $viewType = ($item['view-type']) ? (string)$item['view-type'] : 'horizontal';

        foreach ($item->item as $i => $item) {
            $items[] = $this->parseMessage($item->children()[0]);
        }

        $template = [
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
            $required = ($element->required) ? true : false;

            $el = [
                self::ELEMENT_TYPE => trim((string)$element->element_type),
                self::NAME => trim((string)$element->name),
                self::DISPLAY => trim((string)$element->display),
                self::REQUIRED => $required,
            ];

            if ($el[self::ELEMENT_TYPE] == self::SELECT || $el[self::ELEMENT_TYPE] == self::AUTO_COMPLETE_SELECT) {
                $options = [];

                foreach ($element->options->children() as $option) {
                    $options[trim((string)$option->key)] = trim((string)$option->value);
                }
                $el[self::OPTIONS] = $options;
            }

            $elements[] = $el;
        }

        $autoSubmit = ($item->auto_submit) ? true : false;

        $template = [
            self::TEXT => trim((string)$item->text),
            self::SUBMIT_TEXT => trim((string)$item->submit_text),
            self::CALLBACK => trim((string)$item->callback),
            self::AUTO_SUBMIT => $autoSubmit,
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
}
