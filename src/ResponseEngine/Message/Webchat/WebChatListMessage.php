<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

class WebChatListMessage extends WebChatMessage
{
    protected $messageType = 'list';

    private $items = [];

    private $viewType = 'horizontal';

    /**
     * @param WebChatMessage $message
     * @return $this
     */
    public function addItem(WebChatMessage $message)
    {
        $this->items[] = $message;
        return $this;
    }

    /**
     * @param array $message
     * @return $this
     */
    public function addItems(array $messages)
    {
        foreach ($messages as $message) {
            $this->items[] = $message;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param $viewType
     * @return $this
     */
    public function setViewType($viewType)
    {
        $this->viewType = $viewType;
        return $this;
    }

    /**
     * @return string
     */
    public function getViewType()
    {
        return $this->viewType;
    }

    public function getData()
    {
        return [
            'items' => $this->getItemsArray(),
            'view_type' => $this->getViewType(),
            'disable_text' => $this->getDisableText(),
            'internal' => $this->getInternal(),
            'hidetime' => $this->getHidetime(),
            self::TIME => $this->getTime(),
            self::DATE => $this->getDate()
        ];
    }

    /**
     * @return array
     */
    public function getItemsArray()
    {
        $items = [];

        foreach ($this->items as $message) {
            $items[] = $message->getData() + [
                'message_type' => $message->getMessageType(),
            ];
        }

        return $items;
    }
}
