<?php

declare(strict_types=1);

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\ButtonMessage;
use OpenDialogAi\ResponseEngine\Message\Webchat\Button\BaseButton;

class WebchatButtonMessage extends WebchatMessage implements ButtonMessage
{
    protected $messageType = 'button';

    /** The message buttons. @var BaseButton[] */
    private $buttons = [];

    private $clearAfterInteraction = true;

    /**
     * @param $clearAfterInteraction
     * @return $this
     */
    public function setClearAfterInteraction($clearAfterInteraction)
    {
        $this->clearAfterInteraction = $clearAfterInteraction;
        return $this;
    }

    /**
     * @return bool
     */
    public function getClearAfterInteraction()
    {
        return $this->clearAfterInteraction;
    }

    /**
     * @param BaseButton $button
     * @return $this
     */
    public function addButton(BaseButton $button)
    {
        $this->buttons[] = $button;
        return $this;
    }

    /**
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * {@inheritDoc}
     */
    public function getData():?array
    {
        return parent::getData() + [
                'buttons' => $this->getButtonsArray(),
                'clear_after_interaction' => $this->getClearAfterInteraction()
            ];
    }

    /**
     * @return array
     */
    public function getButtonsArray()
    {
        $buttons = [];

        foreach ($this->buttons as $button) {
            $buttons[] = $button->getData();
        }

        return $buttons;
    }
}
