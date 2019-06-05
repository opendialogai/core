<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

use OpenDialogAi\ResponseEngine\Message\Webchat\Button\BaseWebchatButton;

class WebChatRichMessage extends WebChatMessage
{
    protected $messageType = 'rich';

    private $title;

    private $subTitle;

    private $btnText = null;

    private $btnTabSwitch = false;

    private $btnCallback = null;

    private $btnValue = null;

    private $btnLink = null;

    private $imgSrc = null;

    private $imgLink = null;

    private $imgLinkNewTab = false;

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
     * @param $btnText
     * @return $this
     */
    public function setButtonText($btnText)
    {
        $this->btnText = $btnText;
        return $this;
    }

    /**
     * @param $btnTabSwitch
     * @return $this
     */
    public function setButtonTabSwitch($btnTabSwitch)
    {
        $this->btnTabSwitch = $btnTabSwitch;
        return $this;
    }

    /**
     * @param $btnCallback
     * @return $this
     */
    public function setButtonCallback($btnCallback)
    {
        $this->btnCallback = $btnCallback;
        return $this;
    }

    /**
     * @param $btnValue
     * @return $this
     */
    public function setButtonValue($btnValue)
    {
        $this->btnValue = $btnValue;
        return $this;
    }

    /**
     * @param $btnLink
     * @return $this
     */
    public function setButtonLink($btnLink)
    {
        $this->btnLink = $btnLink;
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
     * @return null|string
     */
    public function getTitle()
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
    public function getButtonText()
    {
        return $this->btnText;
    }

    /**
     * @return bool
     */
    public function getButtonTabSwitch()
    {
        return $this->btnTabSwitch;
    }

    /**
     * @return null|string
     */
    public function getButtonCallback()
    {
        return $this->btnCallback;
    }

    /**
     * @return null|string
     */
    public function getButtonValue()
    {
        return $this->btnValue;
    }

    /**
     * @return null|string
     */
    public function getButtonLink()
    {
        return $this->btnLink;
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
    public function getData()
    {
        $data = [
            'title' => $this->getTitle(),
            'subtitle' => $this->getSubTitle(),
        ];

        if ($this->getButtonText()) {
            $data['button'] = [
                'text' => $this->getButtonText(),
                'tab_switch' => $this->getButtonTabSwitch(),
                'callback' => $this->getButtonCallback(),
                'value' => $this->getButtonValue(),
                'link' => $this->getButtonLink(),
            ];
        }

        if ($this->getImageSrc()) {
            $data['image'] = [
                'src' => $this->getImageSrc(),
                'url' => $this->getImageLink(),
                'link_new_tab' => $this->getImageLinkNewTab(),
            ];
        }

        return parent::getData() + $data;
    }
}
