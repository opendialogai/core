<?php

namespace OpenDialogAi\MessageBuilder;

use OpenDialogAi\MessageBuilder\Message\AttributeMessage;
use OpenDialogAi\MessageBuilder\Message\Button\CallbackButton;
use OpenDialogAi\MessageBuilder\Message\Button\LinkButton;
use OpenDialogAi\MessageBuilder\Message\Button\TabSwitchButton;
use OpenDialogAi\MessageBuilder\Message\ButtonMessage;
use OpenDialogAi\MessageBuilder\Message\EmptyMessage;
use OpenDialogAi\MessageBuilder\Message\Form\AutoCompleteSelectElement;
use OpenDialogAi\MessageBuilder\Message\Form\EmailElement;
use OpenDialogAi\MessageBuilder\Message\Form\RadioElement;
use OpenDialogAi\MessageBuilder\Message\Form\SelectElement;
use OpenDialogAi\MessageBuilder\Message\Form\TextElement;
use OpenDialogAi\MessageBuilder\Message\FormMessage;
use OpenDialogAi\MessageBuilder\Message\FullPageFormMessage;
use OpenDialogAi\MessageBuilder\Message\FullPageRichMessage;
use OpenDialogAi\MessageBuilder\Message\HandToHumanMessage;
use OpenDialogAi\MessageBuilder\Message\Image\Image;
use OpenDialogAi\MessageBuilder\Message\ImageMessage;
use OpenDialogAi\MessageBuilder\Message\ListMessage;
use OpenDialogAi\MessageBuilder\Message\LongTextMessage;
use OpenDialogAi\MessageBuilder\Message\RichMessage;
use OpenDialogAi\MessageBuilder\Message\TextMessage;
use OpenDialogAi\MessageBuilder\Message\TextMessageWithLink;

class MessageMarkUpGenerator
{
    private $disableText;

    private $hideAvatar;

    private $messages = [];

    public function __construct($disableText = false, $hideAvatar = false)
    {
        $this->disableText = ($disableText) ? 'true' : 'false';
        $this->hideAvatar = ($hideAvatar) ? 'true' : 'false';
    }

    /**
     * @param $text
     * @return MessageMarkUpGenerator
     */
    public function addTextMessage($text)
    {
        $this->messages[] = new TextMessage($text);
        return $this;
    }

    /**
     * @param $text
     * @param $link_text
     * @param $link_url
     * @return MessageMarkUpGenerator
     */
    public function addTextMessageWithLink($text, $link_text, $link_url)
    {
        $this->messages[] = new TextMessageWithLink($text, $link_text, $link_url);
        return $this;
    }

    /**
     * @param $src
     * @param $link
     * @param $linkNewTab
     * @return MessageMarkUpGenerator
     */
    public function addImageMessage($src, $link = '', $linkNewTab = false)
    {
        $this->messages[] = new ImageMessage($src, $link, $linkNewTab);
        return $this;
    }

    /**
     * @param $text
     * @param $buttons array
     * @param $external
     * @return MessageMarkUpGenerator
     */
    public function addButtonMessage($text, $buttons, $external = false)
    {
        $buttonMessage = new ButtonMessage($text, $external);
        foreach ($buttons as $button) {
            if (isset($button['tab_switch'])) {
                $buttonMessage->addButton(
                    (new TabSwitchButton($button['text']))
                );
            } elseif (isset($button['link'])) {
                $buttonMessage->addButton(
                    (new LinkButton($button['text'], $button['link'], $button['link_new_tab']))
                );
            } else {
                $buttonMessage->addButton(
                    (new CallbackButton($button['text'], $button['callback'], $button['value']))
                );
            }
        }

        $this->messages[] = $buttonMessage;

        return $this;
    }

    public function addHandToHumanMessage($data)
    {
        $this->messages[] = new HandToHumanMessage($data);
        return $this;
    }

    /**
     * @param $text
     * @return MessageMarkUpGenerator
     */
    public function addAttributeMessage($text)
    {
        $this->messages[] = new AttributeMessage($text);
        return $this;
    }

    /**
     * @param $text
     * @param $submitText
     * @param $callback
     * @param $autoSubmit
     * @param $elements
     * @param $cancelText
     * @param $cancelCallback
     * @return MessageMarkUpGenerator
     */
    public function addFormMessage(
        $text,
        $submitText,
        $callback,
        $autoSubmit,
        $elements,
        $cancelText = null,
        $cancelCallback = null
    ) {
        $formMessage = new FormMessage($text, $submitText, $callback, $autoSubmit, $cancelText, $cancelCallback);
        foreach ($elements as $element) {
            if ($element['element_type'] == 'text') {
                $formMessage->addElement(new TextElement($element['name'], $element['display'], $element['required']));
            } elseif ($element['element_type'] == 'select') {
                $formMessage->addElement(new SelectElement($element['name'], $element['display'], $element['options']));
            } elseif ($element['element_type'] == 'auto_complete_select') {
                $formMessage->addElement(
                    new AutoCompleteSelectElement($element['name'], $element['display'], $element['options'])
                );
            } elseif ($element['element_type'] == 'radio') {
                $formMessage->addElement(new RadioElement($element['name'], $element['display'], $element['options']));
            } elseif ($element['element_type'] == 'email') {
                $formMessage->addElement(new EmailElement($element['name'], $element['display'], $element['required']));
            }
        }

        $this->messages[] = $formMessage;
        return $this;
    }

    /**
     * @param $text
     * @param $submitText
     * @param $callback
     * @param $autoSubmit
     * @param $elements
     * @param null $cancelText
     * @param null $cancelCallback
     * @return MessageMarkUpGenerator
     */
    public function addFullPageFormMessage(
        $text,
        $submitText,
        $callback,
        $autoSubmit,
        $elements,
        $cancelText = null,
        $cancelCallback = null
    ) {
        $formMessage = new FullPageFormMessage($text, $submitText, $callback, $autoSubmit, $cancelText, $cancelCallback);
        foreach ($elements as $element) {
            if ($element['element_type'] == 'text') {
                $formMessage->addElement(new TextElement($element['name'], $element['display'], $element['required']));
            } elseif ($element['element_type'] == 'select') {
                $formMessage->addElement(new SelectElement($element['name'], $element['display'], $element['options']));
            } elseif ($element['element_type'] == 'auto_complete_select') {
                $formMessage->addElement(
                    new AutoCompleteSelectElement($element['name'], $element['display'], $element['options'])
                );
            } elseif ($element['element_type'] == 'radio') {
                $formMessage->addElement(new RadioElement($element['name'], $element['display'], $element['options']));
            } elseif ($element['element_type'] == 'email') {
                $formMessage->addElement(new EmailElement($element['name'], $element['display'], $element['required']));
            }
        }

        $this->messages[] = $formMessage;
        return $this;
    }

    /**
     * @param $title
     * @param $subtitle
     * @param $text
     * @param $buttons
     * @param $image
     * @return MessageMarkUpGenerator
     */
    public function addRichMessage($title, $subtitle, $text, $buttons = [], $image = [])
    {
        $richMessage = new RichMessage($title, $subtitle, $text, $buttons);
        foreach ($buttons as $button) {
            if (isset($button['tab_switch'])) {
                $richMessage->addButton(
                    (new TabSwitchButton($button['text']))
                );
            } elseif (isset($button['link'])) {
                $richMessage->addButton(
                    (new LinkButton($button['text'], $button['link'], $button['link_new_tab']))
                );
            } else {
                $richMessage->addButton(
                    (new CallbackButton($button['text'], $button['callback'], $button['value']))
                );
            }
        }
        if (!empty($image)) {
            $richMessage->addImage(new Image($image['src'], $image['url'], $image['new_tab']));
        }

        $this->messages[] = $richMessage;
        return $this;
    }

    /**
     * @param $title
     * @param $subtitle
     * @param $text
     * @param $buttons
     * @param $image
     * @return MessageMarkUpGenerator
     */
    public function addFullPageRichMessage($title, $subtitle, $text, $buttons = [], $image = [])
    {
        $richMessage = new FullPageRichMessage($title, $subtitle, $text, $buttons);
        foreach ($buttons as $button) {
            if (isset($button['tab_switch'])) {
                $richMessage->addButton(
                    (new TabSwitchButton($button['text']))
                );
            } elseif (isset($button['link'])) {
                $richMessage->addButton(
                    (new LinkButton($button['text'], $button['link'], $button['link_new_tab']))
                );
            } else {
                $richMessage->addButton(
                    (new CallbackButton($button['text'], $button['callback'], $button['value']))
                );
            }
        }
        if (!empty($image)) {
            $richMessage->addImage(new Image($image['src'], $image['url'], $image['new_tab']));
        }

        $this->messages[] = $richMessage;
        return $this;
    }

    /**
     * @param $submitText
     * @param $callback
     * @param $initialText
     * @param $placeholder
     * @param $confirmationText
     * @param $characterLimit
     * @return MessageMarkUpGenerator
     */
    public function addLongTextMessage($submitText, $callback, $initialText, $placeholder, $confirmationText, $characterLimit)
    {
        $this->messages[] = new LongTextMessage(
            $submitText,
            $callback,
            $initialText,
            $placeholder,
            $confirmationText,
            $characterLimit
        );
        return $this;
    }

    /**
     * @return MessageMarkUpGenerator
     */
    public function addEmptyMessage()
    {
        $this->messages[] = new EmptyMessage();
        return $this;
    }

    /**
     * @param $viewType
     * @param $messages
     * @return MessageMarkUpGenerator
     */
    public function addListMessage($viewType, $messages)
    {
        $listMessage = new ListMessage($viewType);
        foreach ($messages as $message) {
            $type = key($message);
            $listMessage->addMessage($type, $message[$type]);
        }

        $this->messages[] = $listMessage;
        return $this;
    }

    public function getMarkUp()
    {
        $markUp = '';

        foreach ($this->messages as $message) {
            $markUp .= $message->getMarkUp();
        }

        return "<message disable_text=\"{$this->disableText}\" hide_avatar=\"{$this->hideAvatar}\">{$markUp}</message>";
    }
}
