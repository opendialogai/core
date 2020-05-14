<?php

namespace OpenDialogAi\MessageBuilder\Message\Button;

class TranscriptDownloadButton extends BaseButton
{
    public $text;

    public $display;

    /**
     * TabSwitchButton constructor.
     * @param $text
     * @param $display
     */
    public function __construct($text, $display = true)
    {
        $this->text = $text;
        $this->display = ($display) ? 'true' : 'false';
    }

    public function getMarkUp()
    {
        return <<<EOT
<button>
    <text>$this->text</text>
    <download>true</download>
    <display>$this->display</display>
</button>
EOT;
    }
}
