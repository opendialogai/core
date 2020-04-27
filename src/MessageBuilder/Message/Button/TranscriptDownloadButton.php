<?php

namespace OpenDialogAi\MessageBuilder\Message\Button;

class TranscriptDownloadButton extends BaseButton
{
    public $text;

    /**
     * TabSwitchButton constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getMarkUp()
    {
        return <<<EOT
<button>
    <text>$this->text</text>
    <download>true</download>
</button>
EOT;
    }
}
