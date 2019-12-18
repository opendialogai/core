<?php

namespace OpenDialogAi\MessageBuilder\Message;

class TextMessageWithLink
{
    public $text;

    public $link_text;

    public $link_url;

    /**
     * TextMessageWithLink constructor.
     * @param $text
     */
    public function __construct($text, $link_text, $link_url)
    {
        $this->text = $text;
        $this->link_text = $link_text;
        $this->link_url = $link_url;
    }

    public function getMarkUp()
    {
        return <<<EOT
<text-message>{$this->text} <link><open-new-tab>true</open-new-tab>
<url>{$this->link_url}</url><text>{$this->link_text}</text></link></text-message>
EOT;
    }
}
