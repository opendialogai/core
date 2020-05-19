<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\ListMessage;
use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;

class WebchatListMessage extends WebchatMessage implements ListMessage
{
    protected $messageType = self::TYPE;

    private $items = [];

    private $viewType = 'horizontal';

    private $title;

    /**
     * @param OpenDialogMessage $message
     * @return $this
     */
    public function addItem(OpenDialogMessage $message)
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

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array
    {
        return [
            'title' => $this->getTitle(),
            'items' => $this->getItemsArray(),
            'view_type' => $this->getViewType(),
            'disable_text' => $this->getDisableText(),
            'internal' => $this->getInternal(),
            'hidetime' => $this->getHidetime(),
            'hideavatar' => $this->getHideAvatar(),
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
