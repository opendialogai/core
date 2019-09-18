<?php

declare(strict_types=1);

namespace OpenDialogAi\ResponseEngine\Message;

use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormElement;

class FormMessage extends OpenDialogMessage
{
    protected $messageType = 'form';

    private $submitText = 'Submit';

    private $autoSubmit = false;

    /** @var FormElement[] */
    private $elements = [];

    private $callbackId = null;

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
    public function getData():?array
    {
        return parent::getData() + [
                'callback_id' => $this->getCallbackId(),
                'elements' => $this->getElementsArray(),
                'auto_submit' => $this->getAutoSubmit(),
                'submit_text' => $this->getSubmitText()
            ];
    }
}
