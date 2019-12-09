<?php

namespace OpenDialogAi\MessageBuilder\Message;

class LongTextMessage
{
    public $submitText;

    public $callback;

    public $initialText;

    public $placeholder;

    public $confirmationText;

    public $characterLimit;

    /**
     * LongTextMessage constructor.
     * @param $submitText
     * @param $callback
     * @param $initialText
     * @param $placeholder
     * @param $confirmationText
     * @param $characterLimit
     */
    public function __construct($submitText, $callback, $initialText, $placeholder, $confirmationText, $characterLimit)
    {
        $this->submitText = $submitText;
        $this->callback = $callback;
        $this->initialText = $initialText;
        $this->placeholder = $placeholder;
        $this->confirmationText = $confirmationText;
        $this->characterLimit = $characterLimit;
    }

    public function getMarkUp()
    {
        return <<<EOT
<long-text-message>
    <submit_text>$this->submitText</submit_text>
    <callback>$this->callback</callback>
    <initial_text>$this->initialText</initial_text>
    <placeholder>$this->placeholder</placeholder>
    <confirmation_text>$this->confirmationText</confirmation_text>
    <character_limit>$this->characterLimit</character_limit>
</long-text-message>
EOT;
    }
}
