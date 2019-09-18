<?php

declare(strict_types=1);

namespace OpenDialogAi\ResponseEngine\Message;

use OpenDialogAi\ResponseEngine\Message\OpenDialogMessage;

class ListMessage extends OpenDialogMessage
{
    protected $messageType = 'list';

    private $items = [];

    private $viewType = 'horizontal';

    /**
     * @param Message $message
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
     * {@inheritDoc}
     */
    public function getData():?array
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
