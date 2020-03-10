<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormElement;

class WebchatBaseFormMessage extends WebchatMessage
{
    private $submitText = 'Submit';

    private $autoSubmit = false;

    /** @var FormElement[] */
    private $elements = [];

    private $callbackId = null;

    private $cancelText = null;

    private $cancelCallback = null;

    /**
     * @param FormElement $element
     * @return $this
     */
    public function addElement(FormElement $element)
    {
        $this->elements[] = $element;
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
     * @param $autoSubmit
     * @return $this
     */
    public function setAutoSubmit($autoSubmit)
    {
        $this->autoSubmit = $autoSubmit;
        return $this;
    }

    /**
     * @return FormElement[]
     */
    public function getElements()
    {
        return $this->elements;
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
     * @return bool
     */
    public function getAutoSubmit()
    {
        return $this->autoSubmit;
    }

    /**
     * @param $callback string
     */
    public function setCancelCallback($callback)
    {
        $this->cancelCallback = $callback;
    }

    /**
     * @return string
     */
    public function getCancelCallback()
    {
        return $this->cancelCallback;
    }

    /**
     * @param $text string
     */
    public function setCancelText($text)
    {
        $this->cancelText = $text;
    }

    /**
     * @return string
     */
    public function getCancelText()
    {
        return $this->cancelText;
    }

    /**
     * @return array
     */
    public function getElementsArray()
    {
        $elements = [];

        foreach ($this->elements as $element) {
            $elements[] = $element->getData();
        }

        return $elements;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array
    {
        return parent::getData() + [
                'callback_id' => $this->getCallbackId(),
                'elements' => $this->getElementsArray(),
                'auto_submit' => $this->getAutoSubmit(),
                'submit_text' => $this->getSubmitText(),
                'cancel_text' => $this->getCancelText(),
                'cancel_callback' => $this->getCancelCallback(),
            ];
    }
}
