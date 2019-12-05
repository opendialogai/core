<?php

namespace OpenDialogAi\MessageBuilder;

use OpenDialogAi\MessageBuilder\Message\AttributeMessage;
use OpenDialogAi\MessageBuilder\Message\ButtonMessage;
use OpenDialogAi\MessageBuilder\Message\ImageMessage;
use OpenDialogAi\MessageBuilder\Message\TextMessage;
use OpenDialogAi\MessageBuilder\Message\TextMessageWithLink;
use OpenDialogAi\MessageBuilder\Message\Button\CallbackButton;
use OpenDialogAi\MessageBuilder\Message\Button\TabSwitchButton;

class MessageMarkUpGenerator
{
    private $messages = [];

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
     * @return MessageMarkUpGenerator
     */
    public function addImageMessage($src, $link = '')
    {
        $this->messages[] = new ImageMessage($src, $link);
        return $this;
    }

    /**
     * @param $text
     * @param $buttons array
     *
     * @return MessageMarkUpGenerator
     */
    public function addButtonMessage($text, $buttons)
    {
        $buttonMessage = new ButtonMessage($text);
        foreach ($buttons as $button) {
            if (isset($button['tab_switch'])) {
                $buttonMessage->addButton(
                    (new TabSwitchButton($button['text']))
                );
            } else {
                $buttonMessage->addButton(
                    (new CallbackButton($button['text'], $button['value'], $button['callback']))
                );
            }
        }

        $this->messages[] = $buttonMessage;

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

    public function getMarkUp()
    {
        $markUp = '';

        foreach ($this->messages as $message) {
            $markUp .= $message->getMarkUp();
        }

        return "<message>{$markUp}</message>";
    }
}
