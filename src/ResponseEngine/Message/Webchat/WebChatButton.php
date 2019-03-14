<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

class WebChatButton
{
    private $text = null;

    private $callbackId = null;

    private $value = null;

    /**
     * @param $text
     * @param $callbackId
     * @param null $value
     */
    public function __construct($text, $callbackId, $value = null)
    {
        $this->text = $text;
        $this->callbackId = $callbackId;
        $this->value = $value;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
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
    public function getText()
    {
        return $this->text;
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
}
