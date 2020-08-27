<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\Webchat\Button\BaseButton;

abstract class WebchatBaseRichMessage extends WebchatMessage
{
    private $title;

    private $subTitle;

    private $callback;

    private $callbackValue;

    private $link;

    private $imgSrc = null;

    private $imgLink = null;

    private $imgLinkNewTab = false;

    /** The message buttons. @var BaseButton[] */
    private $buttons = [];

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
     * @param $subTitle
     * @return $this
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;
        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @param $callbackValue
     * @return $this
     */
    public function setCallbackValue($callbackValue)
    {
        $this->callbackValue = $callbackValue;
        return $this;
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
     * @param $imgSrc
     * @return $this
     */
    public function setImageSrc($imgSrc)
    {
        $this->imgSrc = $imgSrc;
        return $this;
    }

    /**
     * @param $imgLink
     * @return $this
     */
    public function setImageLink($imgLink)
    {
        $this->imgLink = $imgLink;
        return $this;
    }

    /**
     * @param $imgLinkNewTab
     * @return $this
     */
    public function setImageLinkNewTab($imgLinkNewTab)
    {
        $this->imgLinkNewTab = $imgLinkNewTab;
        return $this;
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
     * {@inheritDoc}
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return null|string
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * @return null|string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return null|string
     */
    public function getCallbackValue()
    {
        return $this->callbackValue;
    }

    /**
     * @return null|string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return null|string
     */
    public function getImageSrc()
    {
        return $this->imgSrc;
    }

    /**
     * @return null|string
     */
    public function getImageLink()
    {
        return $this->imgLink;
    }

    /**
     * @return bool
     */
    public function getImageLinkNewTab()
    {
        return $this->imgLinkNewTab;
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
    public function getData(): ?array
    {
        $data = [
            'title' => $this->getTitle(),
            'subtitle' => $this->getSubTitle(),
            'callback' => $this->getCallback(),
            'callback_value' => $this->getCallbackValue(),
            'link' => $this->getLink(),
            'buttons' => $this->getButtonsArray(),
        ];

        if ($this->getImageSrc()) {
            $data['image'] = [
                'src' => $this->getImageSrc(),
                'url' => $this->getImageLink(),
                'link_new_tab' => $this->getImageLinkNewTab(),
            ];
        }

        return parent::getData() + $data;
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
