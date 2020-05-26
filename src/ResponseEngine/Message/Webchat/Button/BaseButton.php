<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

abstract class BaseButton
{
    protected $text = null;

    protected $display = true;

    protected $type = "";

    /**
     * @param $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return bool
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getData()
    {
        return [
            'text' => $this->getText(),
            'display' => $this->getDisplay(),
            'type' => $this->getType(),
        ];
    }
}
