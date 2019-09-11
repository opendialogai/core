<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

abstract class BaseButton
{
    protected $text = null;

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
     * @return null|string
     */
    public function getText()
    {
        return $this->text;
    }

    public function getData()
    {
        return [
            'text' => $this->getText()
        ];
    }
}
