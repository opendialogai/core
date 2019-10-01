<?php

namespace OpenDialogAi\ResponseEngine\Message;

interface ImageMessage extends OpenDialogMessage
{
    /**
     * @param $imgSrc
     * @return $this
     */
    public function setImgSrc($imgSrc);

    /**
     * @param $imgLink
     * @return $this
     */
    public function setImgLink($imgLink);

    /**
     * @param $linkNewTab
     * @return $this
     */
    public function setLinkNewTab($linkNewTab);

    /**
     * @return null|string
     */
    public function getImgSrc();

    /**
     * @return null|string
     */
    public function getImgLink();

    /**
     * @return bool
     */
    public function getLinkNewTab();

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array;
}
