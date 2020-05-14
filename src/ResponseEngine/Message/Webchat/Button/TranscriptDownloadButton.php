<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class TranscriptDownloadButton extends BaseButton
{
    /**
     * @param $text
     * @param $display
     */
    public function __construct($text, $display = true)
    {
        $this->text = $text;
        $this->display = $display;
    }

    public function getData()
    {
        return parent::getData() + [
            'download' => true
        ];
    }
}
