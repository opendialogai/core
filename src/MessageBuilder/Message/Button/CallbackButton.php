<?php

namespace OpenDialogAi\MessageBuilder\Message\Button;

class CallbackButton extends BaseButton
{
    public $text;

    public $value;

    public $callbackId;

    public $type;

    /**
     * CallbackButton constructor.
     * @param $text
     * @param $callbackId
     * @param $value
     * @param bool $display
     * @param string $type
     */
    public function __construct($text, $callbackId, $value, $display = true, $type = "")
    {
        $this->text = $text;
        $this->callbackId = $callbackId;
        $this->value = $value;
        $this->display = ($display) ? 'true' : 'false';
        $this->type = $type;
    }

    public function getMarkUp()
    {
        $typeProperty = $this->type != "" ? " type='$this->type'" : "";

        return <<<EOT
<button$typeProperty>
    <text>$this->text</text>
    <value>$this->value</value>
    <callback>$this->callbackId</callback>
    <display>$this->display</display>
</button>
EOT;
    }
}
