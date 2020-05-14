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
     * @param $display
     */
    public function __construct($text, $callbackId, $value, $display = true)
    {
        $this->text = $text;
        $this->callbackId = $callbackId;
        $this->value = $value;
        $this->display = ($display) ? 'true' : 'false';
    }

    public function getMarkUp()
    {
        return <<<EOT
<button>
    <text>$this->text</text>
    <value>$this->value</value>
    <callback>$this->callbackId</callback>
    <display>$this->display</display>
</button>
EOT;
    }
}
