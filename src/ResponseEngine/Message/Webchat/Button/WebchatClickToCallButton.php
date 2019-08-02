<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class WebchatClickToCallButton extends BaseWebchatButton
{
    protected $phoneNumber = null;

    /**
     * @param $text
     * @param $phoneNumber
     */
    public function __construct($text, $phoneNumber)
    {
        $this->text = $text;
        $this->phoneNumber = $phoneNumber;
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
