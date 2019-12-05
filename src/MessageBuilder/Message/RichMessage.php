<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Button\BaseButton;
use OpenDialogAi\MessageBuilder\Message\Image\Image;

class RichMessage
{
    public $title;

    public $subtitle;

    public $text;

    /** @var BaseButton[] */
    public $buttons = [];

    public $image;

    /**
     * RichMessage constructor.
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

    public function getMarkUp()
    {
        $buttonMarkUp = '';

        foreach ($this->buttons as $button) {
            $buttonMarkUp .= $button->getMarkUp();
        }

        $imageMarkUp = '';

        if ($this->image) {
            $imageMarkUp = $this->image->getMarkUp();
        }

        return <<<EOT
<rich-message>
    <title>$this->title</title>
    <subtitle>$this->subtitle</subtitle>
    <text>$this->text</text>
    $buttonMarkUp
    $imageMarkUp
</rich-message>
EOT;
    }
}
