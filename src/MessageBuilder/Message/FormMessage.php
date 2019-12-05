<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Form\BaseElement;

class FormMessage
{
    public $text;

    public $submitText;

    public $callback;

    public $autoSubmit;

    /** @var BaseElement[] */
    public $elements = [];

    /**
     * FormMessage constructor.
     * @param $text
     * @param $submitText
     * @param $callback
     * @param $autoSubmit
     */
    public function __construct($text, $submitText, $callback, $autoSubmit)
    {
        $this->text = $text;
        $this->submitText = $submitText;
        $this->callback = $callback;
        $this->autoSubmit = $autoSubmit;
    }

    public function addElement(BaseElement $element)
    {
        $this->elements[] = $element;
    }

    public function getMarkUp()
    {
        $elementMarkup = '';

        foreach ($this->elements as $element) {
            $elementMarkup .= $element->getMarkUp();
        }

        return <<<EOT
<form-message>
    <text>$this->text</text>
    <submit_text>$this->submitText</submit_text>
    <callback>$this->callback</callback>
    <auto_submit>$this->autoSubmit</auto_submit>
    $elementMarkup
</form-message>
EOT;
    }
}
