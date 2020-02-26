<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Button\BaseButton;
use OpenDialogAi\MessageBuilder\Message\Image\Image;

abstract class BaseRichMessage
{
    public $title;

    public $subtitle;

    public $text;

    /** @var BaseButton[] */
    public $buttons = [];

    /** @var Image */
    public $image;

    /**
     * @param $title
     * @param $subtitle
     * @param $text
     */
    public function __construct($title, $subtitle, $text)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->text = $text;
    }

    public function addButton(BaseButton $button)
    {
        $this->buttons[] = $button;
    }

    public function addImage(Image $image)
    {
        $this->image = $image;
    }

    protected function getButtonMarkUp()
    {
        $buttonMarkUp = '';

        foreach ($this->buttons as $button) {
            $buttonMarkUp .= $button->getMarkUp();
        }

        return $buttonMarkUp;
    }

    protected function getImageMarkUp()
    {
        $imageMarkUp = '';

        if ($this->image) {
            $imageMarkUp = $this->image->getMarkUp();
        }

        return $imageMarkUp;
    }
}
