<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Form\BaseElement;

abstract class BaseFormMessage
{
    public $text;

    public $submitText;

    public $callback;

    public $autoSubmit;

    /** @var BaseElement[] */
    public $elements = [];

    /**
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
}
