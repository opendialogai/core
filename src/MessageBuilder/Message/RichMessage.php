<?php

namespace OpenDialogAi\MessageBuilder\Message;

class RichMessage extends BaseRichMessage
{
    public $callback;

    public $callbackValue;

    /**
     * @param $title
     * @param $subtitle
     * @param $text
     * @param $callback
     * @param $callbackValue
     */
    public function __construct($title, $subtitle, $text, $callback = '', $callbackValue = '')
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->text = $text;
        $this->callback = $callback;
        $this->callbackValue = $callbackValue;
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
    $buttonMarkUp
    $imageMarkUp
</rich-message>
EOT;
    }
}
