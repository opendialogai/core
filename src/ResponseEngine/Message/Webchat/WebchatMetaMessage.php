<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\MetaMessage;

class WebchatMetaMessage extends WebchatMessage implements MetaMessage
{
    protected $messageType = self::TYPE;

    private $elements = [];

    /**
     * @param $submitText
     * @return $this
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
        return $this;
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return array
     */
    public function getElementsArray()
    {
        $elements = [];

        foreach ($this->elements as $key => $element) {
            $elements[$key] = $element;
        }

        return $elements;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array
    {
        return [
            'elements' => $this->getElementsArray(),
            'disable_text' => $this->getDisableText(),
            'internal' => $this->getInternal(),
            'hidetime' => $this->getHidetime(),
            self::TIME => $this->getTime(),
            self::DATE => $this->getDate()
        ];
    }
}
