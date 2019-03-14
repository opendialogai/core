<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

class WebChatButtonMessage extends WebChatMessage
{
    protected $messageType = 'button';

    /** The message buttons. @var WebChatButton[] */
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
     * @param WebChatButton $button
     * @return $this
     */
    public function addButton(WebChatButton $button)
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
     * @return array
     */
    public function getData()
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
            $buttons[] = [
                'text' => $button->getText(),
                'callback_id' => $button->getCallbackId(),
                'value' => $button->getValue()
            ];
        }

        return $buttons;
    }
}
