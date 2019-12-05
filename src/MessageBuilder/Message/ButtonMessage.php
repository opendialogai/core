<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Button\BaseButton;

class ButtonMessage
{
    public $text;

    /** @var BaseButton[] */
    public $buttons = [];

    /**
     * WebchatButtonMessage constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function addButton(BaseButton $button)
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
