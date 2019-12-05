<?php

namespace OpenDialogAi\MessageBuilder\Message;

class ImageMessage
{
    public $src;
    public $link;
    public $linkNewTab;

    /**
     * ImageMessage constructor.
     * @param $text
     */
    public function __construct($src, $link, $linkNewTab)
    {
        $this->src = $src;
        $this->link = $link;
        $this->linkNewTab = ($linkNewTab) ? 'true' : 'false';
    }

    public function getMarkUp()
    {
        return <<<EOT
<image-message>
    <src>{$this->src}</src>
    <link new_tab="$this->linkNewTab">{$this->link}</link>
</image-message>
EOT;
    }
}
