<?php

namespace OpenDialogAi\Core\Tests\Utils;

/**
 * To help with generating message mark up
 */
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
        $markUp = "";

        foreach ($this->messages as $message) {
            $markUp .= $message->getMarkUp();
        }

        return "<message>{$markUp}</message>";
    }
}

class TextMessage
{
    public $text;

    /**
     * TextMessage constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    function getMarkUp()
    {
        return <<<EOT
<text-message>{$this->text}</text-message>
EOT;
    }
}

class TextMessageWithLink
{
    public $text;
    public $link_text;
    public $link_url;

    /**
     * TextMessage constructor.
     * @param $text
     */
    public function __construct($text, $link_text, $link_url)
    {
        $this->text = $text;
        $this->link_text = $link_text;
        $this->link_url = $link_url;
    }

    function getMarkUp()
    {
        return <<<EOT
<text-message>{$this->text} <link><open-new-tab>true</open-new-tab><url>{$this->link_url}</url><text>{$this->link_text}</text></link></text-message>
EOT;
    }
}

class ImageMessage
{
    public $src;
    public $link;

    /**
     * ImageMessage constructor.
     * @param $text
     */
    public function __construct($src, $link)
    {
        $this->src = $src;
        $this->link = $link;
    }

    function getMarkUp()
    {
        return <<<EOT
<image-message><link>{$this->link}</link><src>{$this->src}</src></image-message>
EOT;
    }
}

class ButtonMessage
{
    public $text;

    /** @var Button[] */
    public $buttons = [];

    /**
     * ButtonMessage constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function addButton(Button $button)
    {
        $this->buttons[] = $button;
    }

    public function getMarkUp()
    {
        $buttonMarkUp = "";

        foreach ($this->buttons as $button) {
            $buttonMarkUp .= $button->getMarkUp();
        }

        return <<<EOT
<button-message>
    <text>$this->text</text>
    $buttonMarkUp
</button-message>
EOT;
    }
}

abstract class Button
{
}

class CallbackButton extends Button
{
    public $text;
    public $value;
    public $callbackId;

    /**
     * CallbackButton constructor.
     * @param $text
     * @param $callbackId
     * @param $value
     */
    public function __construct($text, $value, $callbackId)
    {
        $this->text = $text;
        $this->callbackId = $callbackId;
        $this->value = $value;
    }

    public function getMarkUp()
    {
        return <<<EOT
<button>
    <text>$this->text</text>
    <value>$this->value</value>
    <callback>$this->callbackId</callback>
</button>
EOT;
    }
}

class TabSwitchButton extends Button
{
    public $text;

    /**
     * TabSwitchButton constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getMarkUp()
    {
        return <<<EOT
<button>
    <text>$this->text</text>
    <tab_switch>true</tab_switch>
</button>
EOT;
    }
}

class AttributeMessage
{
    public $text;

    /**
     * Attribute Message constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    function getMarkUp()
    {
        return <<<EOT
<attribute-message>{$this->text}</attribute-message>
EOT;
    }
}
