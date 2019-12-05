<?php

namespace OpenDialogAi\MessageBuilder\Message;

class AttributeMessage
{
    public $text;

    /**
     * AttributeMessage constructor.
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getMarkUp()
    {
        return <<<EOT
<attribute-message>{$this->text}</attribute-message>
EOT;
    }
}
