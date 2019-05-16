<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class WebchatTabSwitchButton extends BaseWebchatButton
{
    /**
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getData()
    {
        return parent::getData() + [
            'tab_switch' => true
        ];
    }
}
