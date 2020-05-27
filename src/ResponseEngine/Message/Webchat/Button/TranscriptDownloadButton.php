<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class TranscriptDownloadButton extends BaseButton
{
    /**
     * @param $text
     * @param bool $display
     * @param string $type
     */
    public function __construct($text, $display = true, $type = "")
    {
        $this->text = $text;
        $this->display = $display;
        $this->type = $type;
    }

    public function getData()
    {
        return parent::getData() + [
            'download' => true
        ];
    }
}
