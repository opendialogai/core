<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class ClickToCallButton extends BaseButton
{
    protected $phoneNumber = null;

    /**
     * @param $text
     * @param $phoneNumber
     * @param $display
     */
    public function __construct($text, $phoneNumber, $display = true)
    {
        $this->text = $text;
        $this->phoneNumber = $phoneNumber;
        $this->display = $display;
    }

    /**
     * @param $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function getData()
    {
        return parent::getData() + [
            'phone_number' => $this->getPhoneNumber(),
        ];
    }
}
