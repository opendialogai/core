<?php

namespace OpenDialogAi\ResponseEngine\Message;

use OpenDialogAi\ResponseEngine\Message\Webchat\Button\BaseButton;

interface ButtonMessage extends OpenDialogMessage
{
    const TYPE = 'button';

    /**
     * @param $external
     * @return $this
     */
    public function setExternal($external);

    /**
     * @return bool
     */
    public function getExternal();

    /**
     * @param $clearAfterInteraction
     * @return $this
     */
    public function setClearAfterInteraction($clearAfterInteraction);

    /**
     * @return bool
     */
    public function getClearAfterInteraction();

    /**
     * @param BaseButton $button
     * @return $this
     */
    public function addButton(BaseButton $button);

    /**
     * @return array
     */
    public function getButtons();

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array;

    /**
     * @return array
     */
    public function getButtonsArray();
}
