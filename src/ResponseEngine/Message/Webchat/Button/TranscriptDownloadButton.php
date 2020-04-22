<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Button;

class TranscriptDownloadButton extends BaseButton
{
    /**
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getData()
    {
        return parent::getData() + [
            'download' => true
        ];
    }
}
