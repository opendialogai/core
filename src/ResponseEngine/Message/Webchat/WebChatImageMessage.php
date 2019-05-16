<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat;

class WebChatImageMessage extends WebChatMessage
{
    protected $messageType = 'image';

    private $imgSrc = null;

    private $imgLink = null;

    private $linkNewTab = true;

    /**
     * @param $imgSrc
     * @return $this
     */
    public function setImgSrc($imgSrc)
    {
        $this->imgSrc = $imgSrc;
        return $this;
    }

    /**
     * @param $imgLink
     * @return $this
     */
    public function setImgLink($imgLink)
    {
        $this->imgLink = $imgLink;
        return $this;
    }

    /**
     * @param $linkNewTab
     * @return $this
     */
    public function setLinkNewTab($linkNewTab)
    {
        $this->linkNewTab = $linkNewTab;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getImgSrc()
    {
        return $this->imgSrc;
    }

    /**
     * @return null|string
     */
    public function getImgLink()
    {
        return $this->imgLink;
    }

    /**
     * @return bool
     */
    public function getLinkNewTab()
    {
        return $this->linkNewTab;
    }

    public function getData()
    {
        return [
            'img_src' => $this->getImgSrc(),
            'img_link' => $this->getImgLink(),
            'link_new_tab' => $this->getLinkNewTab(),
            'disable_text' => $this->getDisableText(),
            'internal' => $this->getInternal(),
            'hidetime' => $this->getHidetime(),
            'time' => $this->getTime(),
            'date' => $this->getDate()
        ];
    }
}
