<?php

namespace OpenDialogAi\MessageBuilder\Message\Button;

class LinkButton extends BaseButton
{
    public $text;

    public $link;

    public $linkNewTab;

    public $display;

    public $type;

    /**
     * LinkButton constructor.
     * @param $text
     * @param $link
     * @param $linkNewTab
     * @param bool $display
     * @param string $type
     */
    public function __construct($text, $link, $linkNewTab, $display = true, $type = "")
    {
        $this->text = $text;
        $this->link = $link;
        $this->linkNewTab = ($linkNewTab) ? 'true' : 'false';
        $this->display = ($display) ? 'true' : 'false';
        $this->type = $type;
    }

    public function getMarkUp()
    {
        $typeProperty = $this->type != "" ? " type='$this->type'" : "";

        return <<<EOT
<button$typeProperty>
    <text>$this->text</text>
    <link new_tab="$this->linkNewTab">$this->link</link>
    <display>$this->display</display>
</button>
EOT;
    }
}
