<?php

namespace OpenDialogAi\ResponseEngine\Message;

use OpenDialogAi\ResponseEngine\Message\Webchat\Button\BaseButton;

interface RichMessage extends OpenDialogMessage
{
    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @param $subTitle
     * @return $this
     */
    public function setSubTitle($subTitle);

    /**
     * @param $imgSrc
     * @return $this
     */
    public function setImageSrc($imgSrc);

    /**
     * @param $imgLink
     * @return $this
     */
    public function setImageLink($imgLink);

    /**
     * @param $imgLinkNewTab
     * @return $this
     */
    public function setImageLinkNewTab($imgLinkNewTab);

    /**
     * @param BaseButton $button
     * @return $this
     */
    public function addButton(BaseButton $button);

    /**
     * {@inheritDoc}
     */
    public function getTitle(): ?string;

    /**
     * @return null|string
     */
    public function getSubTitle();

    /**
     * @return null|string
     */
    public function getImageSrc();

    /**
     * @return null|string
     */
    public function getImageLink();

    /**
     * @return bool
     */
    public function getImageLinkNewTab();

    /**
     * @return array
     */
    public function getButtons();

    /**
     * {@inheritDoc}
     */
    public function getData(): ?array;

    /**
     * @return array
     */
    public function getButtonsArray();
}
