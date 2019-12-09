<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Button\BaseButton;

class ButtonMessage
{
    public $text;

    public $external;

    /** @var BaseButton[] */
    public $buttons = [];

    /**
     * ButtonMessage constructor.
     * @param $text
     * @param $external
     */
    public function __construct($text, $external)
    {
        $this->text = $text;
        $this->external = ($external) ? 'true' : 'false';
    }

    public function addButton(BaseButton $button)
    {
        $this->buttons[] = $button;
    }

    public function getMarkUp()
    {
        $buttonMarkUp = '';

        foreach ($this->buttons as $button) {
            $buttonMarkUp .= $button->getMarkUp();
        }

        return <<<EOT
<button-message>
    <text>$this->text</text>
    <external>$this->external</external>
    $buttonMarkUp
</button-message>
EOT;
    }
}
