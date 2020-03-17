<?php

namespace OpenDialogAi\MessageBuilder\Message;

class CtaMessage
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
<cta-message>{$this->text}</cta-message>
EOT;
    }
}
