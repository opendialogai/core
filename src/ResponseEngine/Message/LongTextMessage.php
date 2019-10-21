<?php

namespace OpenDialogAi\ResponseEngine\Message;

interface LongTextMessage extends OpenDialogMessage
{
    const TYPE = 'long_text';

    /**
     * @param $characterLimit
     * @return $this
     */
    public function setCharacterLimit($characterLimit);

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
     * @param $initialText
     * @return $this
     */
    public function setInitialText($initialText);

    /**
     * @param $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder);

    /**
     * @param $confirmationText
     * @return $this
     */
    public function setConfirmationText($confirmationText);

    /**
     * @return null|int
     */
    public function getCharacterLimit();

    /**
     * @return null|string
     */
    public function getSubmitText();

    /**
     * @return null|string
     */
    public function getCallbackId();

    /**
     * @return null|string
     */
    public function getInitialText();

    /**
     * @return null|string
     */
    public function getPlaceholder();

    /**
     * @return null|string
     */
    public function getConfirmationText();

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array;
}
