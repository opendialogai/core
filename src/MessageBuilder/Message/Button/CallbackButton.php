<?php

namespace OpenDialogAi\MessageBuilder\Message\Button;

class CallbackButton extends BaseButton
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
