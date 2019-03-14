<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

class WebChatLongTextMessage extends WebChatMessage
{
    protected $messageType = 'longtext';

    private $characterLimit = null;

    private $submitText = null;

    private $callbackId = null;

    private $initialText = null;

    private $placeholder = null;

    private $confirmationText = null;

    /**
     * @param $characterLimit
     * @return $this
     */
    public function setCharacterLimit($characterLimit)
    {
        $this->characterLimit = $characterLimit;
        return $this;
    }

    /**
     * @param $submitText
     * @return $this
     */
    public function setSubmitText($submitText)
    {
        $this->submitText = $submitText;
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
     * @param $initialText
     * @return $this
     */
    public function setInitialText($initialText)
    {
        $this->initialText = $initialText;
        return $this;
    }

    /**
     * @param $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param $confirmationText
     * @return $this
     */
    public function setConfirmationText($confirmationText)
    {
        $this->confirmationText = $confirmationText;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getCharacterLimit()
    {
        return $this->characterLimit;
    }

    /**
     * @return null|string
     */
    public function getSubmitText()
    {
        return $this->submitText;
    }

    /**
     * @return null|string
     */
    public function getCallbackId()
    {
        return $this->callbackId;
    }

    /**
     * @return null|string
     */
    public function getInitialText()
    {
        return $this->initialText;
    }

    /**
     * @return null|string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @return null|string
     */
    public function getConfirmationText()
    {
        return $this->confirmationText;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return parent::getData() + [
            'character_limit' => $this->getCharacterLimit(),
            'submit_text' => $this->getSubmitText(),
            'callback_id' => $this->getCallbackId(),
            'initial_text' => $this->getInitialText(),
            'placeholder' => $this->getPlaceholder(),
            'confirmation_text' => $this->getConfirmationText()
        ];
    }
}
