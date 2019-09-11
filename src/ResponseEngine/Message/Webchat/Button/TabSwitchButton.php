<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class TabSwitchButton extends BaseButton
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
