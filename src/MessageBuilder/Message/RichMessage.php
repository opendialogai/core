<?php

namespace OpenDialogAi\MessageBuilder\Message;

class RichMessage extends BaseRichMessage
{
    public $callback;

    public $callbackValue;

    public $link;

    /**
     * @param $title
     * @param $subtitle
     * @param $text
     * @param $callback
     * @param $callbackValue
     * @param $link
     */
    public function __construct($title, $subtitle, $text, $callback = '', $callbackValue = '', $link = '')
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->text = $text;
        $this->callback = $callback;
        $this->callbackValue = $callbackValue;
        $this->link = $link;
    }

    public function getMarkUp()
    {
        $buttonMarkUp = $this->getButtonMarkUp();

        $imageMarkUp = $this->getImageMarkUp();

        return <<<EOT
<rich-message>
    <title>$this->title</title>
    <subtitle>$this->subtitle</subtitle>
    <text>$this->text</text>
    <callback>$this->callback</callback>
    <callback_value>$this->callbackValue</callback_value>
    <link>$this->link</link>
    $buttonMarkUp
    $imageMarkUp
</rich-message>
EOT;
    }
}
