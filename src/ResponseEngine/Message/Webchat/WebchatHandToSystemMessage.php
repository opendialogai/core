<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\HandToSystemMessage;

class WebchatHandToSystemMessage extends WebchatMessage implements HandToSystemMessage
{
    protected $messageType = self::TYPE;

    private $system;

    private $elements = [];

    /**
     * @return string
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @param string $system
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * @param $elements
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
            'system' => $this->getSystem(),
            'elements' => $this->getElementsArray(),
            'disable_text' => $this->getDisableText(),
            'internal' => $this->getInternal(),
            'hidetime' => $this->getHidetime(),
            self::TIME => $this->getTime(),
            self::DATE => $this->getDate()
        ];
    }
}
