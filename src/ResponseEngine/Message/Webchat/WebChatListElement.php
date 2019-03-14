<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

class WebChatListElement
{
    private $title = null;

    private $subTitle = null;

    private $image = null;

    private $buttonText = null;

    private $buttonCallback = null;

    private $buttonUrl = null;

    private $buttonLinkNewTab = true;

    /**
     * @param $title
     * @param $subTitle
     * @param $image
     */
    public function __construct($title, $subTitle, $image)
    {
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->image = $image;
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
     * @param $subTitle
     * @return $this
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;
        return $this;
    }

    /**
     * @param $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param $buttonText
     * @return $this
     */
    public function setButtonText($buttonText)
    {
        $this->buttonText = $buttonText;
        return $this;
    }

    /**
     * @param $buttonCallback
     * @return $this
     */
    public function setButtonCallback($buttonCallback)
    {
        $this->buttonCallback = $buttonCallback;
        return $this;
    }

    /**
     * @param $buttonUrl
     * @return $this
     */
    public function setButtonUrl($buttonUrl)
    {
        $this->buttonUrl = $buttonUrl;
        return $this;
    }

    /**
     * @param $buttonLinkNewTab
     * @return $this
     */
    public function setButtonLinkNewTab($buttonLinkNewTab)
    {
        $this->buttonLinkNewTab = $buttonLinkNewTab;
        return $this;
    }

    /**
     * @param $buttonText
     * @param $buttonCallback
     * @return $this
     */
    public function addCallbackButton($buttonText, $buttonCallback)
    {
        $this->buttonText = $buttonText;
        $this->buttonCallback = $buttonCallback;
        return $this;
    }

    /**
     * @param $buttonText
     * @param $buttonUrl
     * @param $buttonLinkNewTab
     * @return $this
     */
    public function addLinkButton($buttonText, $buttonUrl, $buttonLinkNewTab = true)
    {
        $this->buttonText = $buttonText;
        $this->buttonUrl = $buttonUrl;
        $this->buttonLinkNewTab = $buttonLinkNewTab;
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
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return null|string
     */
    public function getButtonText()
    {
        return $this->buttonText;
    }

    /**
     * @return null|string
     */
    public function getButtonCallback()
    {
        return $this->buttonCallback;
    }

    /**
     * @return null|string
     */
    public function getButtonUrl()
    {
        return $this->buttonUrl;
    }

    /**
     * @return null|string
     */
    public function getButtonLinkNewTab()
    {
        return $this->buttonLinkNewTab;
    }
}
