<?php

namespace OpenDialogAi\MessageBuilder\Message\Button;

class LinkButton extends BaseButton
{
    public $text;

    public $link;

    public $linkNewTab;

    /**
     * LinkButton constructor.
     * @param $text
     * @param $link
     * @param $linkNewTab
     */
    public function __construct($text, $link, $linkNewTab)
    {
        $this->text = $text;
        $this->link = $link;
        $this->linkNewTab = ($linkNewTab) ? 'true' : 'false';
    }

    public function getMarkUp()
    {
        return <<<EOT
<button>
    <text>$this->text</text>
    <link new_tab="$this->linkNewTab">$this->link</link>
</button>
EOT;
    }
}
