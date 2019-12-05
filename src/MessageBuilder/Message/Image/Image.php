<?php

namespace OpenDialogAi\MessageBuilder\Message\Image;

class Image
{
    public $src;
    public $url;
    public $linkNewTab;

    /**
     * @param $src
     * @param $url
     * @param $linkNewTab
     */
    public function __construct($src, $url, $linkNewTab)
    {
        $this->src = $src;
        $this->url = $url;
        $this->linkNewTab = $linkNewTab;
    }

    public function getMarkUp()
    {
        return <<<EOT
<image>
    <src>$this->src</src>
    <url new_tab="$this->linkNewTab">$this->url</url>
</image>
EOT;
    }
}
