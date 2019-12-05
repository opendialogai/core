<?php

namespace OpenDialogAi\MessageBuilder\Message;

class TextMessage
{
    public $text;

    /**
     * TextMessage constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getMarkUp()
    {
        return <<<EOT
<text-message>{$this->text}</text-message>
EOT;
    }
}
