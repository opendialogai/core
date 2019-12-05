<?php

namespace OpenDialogAi\MessageBuilder\Message;

class ImageMessage
{
    public $src;
    public $link;

    /**
     * ImageMessage constructor.
     * @param $text
     */
    public function __construct($src, $link)
    {
        $this->src = $src;
        $this->link = $link;
    }

    public function getMarkUp()
    {
        return <<<EOT
<image-message><link>{$this->link}</link><src>{$this->src}</src></image-message>
EOT;
    }
}
