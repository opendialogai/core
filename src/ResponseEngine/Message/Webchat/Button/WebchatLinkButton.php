<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class WebchatLinkButton extends BaseWebchatButton
{
    protected $link = null;

    /**
     * @param $text
     * @param $link
     */
    public function __construct($text, $link)
    {
        $this->text = $text;
        $this->link = $link;
    }

    /**
     * @param $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLink()
    {
        return $this->link;
    }

    public function getData()
    {
        return parent::getData() + [
            'link' => $this->getLink()
        ];
    }
}
