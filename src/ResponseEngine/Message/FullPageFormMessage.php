<?php

namespace OpenDialogAi\ResponseEngine\Message;

use OpenDialogAi\ResponseEngine\Message\Webchat\Form\FormElement;

interface FullPageFormMessage extends OpenDialogMessage
{
    const TYPE = 'fp-form';

    /**
     * @param FormElement $element
     * @return $this
     */
    public function addElement(FormElement $element);

    /**
     * @param $submitText
     * @return $this
     */
    public function setSubmitText($submitText);

    /**
     * @param $callbackId
     * @return $this
     */
    public function setCallbackId($callbackId);

    /**
     * @param $autoSubmit
     * @return $this
     */
    public function setAutoSubmit($autoSubmit);

    /**
     * @return FormElement[]
     */
    public function getElements();

    /**
     * @return null|string
     */
    public function getSubmitText();

    /**
     * @return null|string
     */
    public function getCallbackId();

    /**
     * @return bool
     */
    public function getAutoSubmit();

    /**
     * @return array
     */
    public function getElementsArray();

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array;
}
