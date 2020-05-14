<?php

namespace OpenDialogAi\MessageBuilder\Message\Button;

class LinkButton extends BaseButton
{
    public $text;

    public $link;

    public $linkNewTab;

    public $display;

    /**
     * LinkButton constructor.
     * @param $text
     * @param $link
     * @param $linkNewTab
     * @param $display
     */
    public function __construct($text, $link, $linkNewTab, $display = true)
    {
        $this->text = $text;
        $this->link = $link;
        $this->linkNewTab = ($linkNewTab) ? 'true' : 'false';
        $this->display = ($display) ? 'true' : 'false';
    }

    public function getMarkUp()
    {
        return <<<EOT
<button>
    <text>$this->text</text>
    <link new_tab="$this->linkNewTab">$this->link</link>
    <display>$this->display</display>
</button>
EOT;
    }
}
