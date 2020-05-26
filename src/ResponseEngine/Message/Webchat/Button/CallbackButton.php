<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class CallbackButton extends BaseButton
{
    protected $callbackId = null;

    protected $value = null;

    /**
     * @param $text
     * @param $callbackId
     * @param null $value
     * @param bool $display
     * @param string $type
     */
    public function __construct($text, $callbackId, $value = null, $display = true, $type = "")
    {
        $this->text = $text;
        $this->callbackId = $callbackId;
        $this->value = $value;
        $this->display = $display;
        $this->type = $type;
    }

    /**
     * @param $callbackId
     * @return $this
     */
    public function setCallbackId($callbackId)
    {
        $this->callbackId = $callbackId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCallbackId()
    {
        return $this->callbackId;
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getData()
    {
        return parent::getData() + [
            'callback_id' => $this->getCallbackId(),
            'value' => $this->getValue()
        ];
    }
}
