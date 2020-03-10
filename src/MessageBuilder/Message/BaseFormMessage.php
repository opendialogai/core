<?php

namespace OpenDialogAi\MessageBuilder\Message;

use OpenDialogAi\MessageBuilder\Message\Form\BaseElement;

abstract class BaseFormMessage
{
    public $text;

    public $submitText;

    public $callback;

    public $autoSubmit;

    public $cancelText;

    public $cancelCallback;

    /** @var BaseElement[] */
    public $elements = [];

    /**
     * @param $text
     * @param $submitText
     * @param $callback
     * @param $autoSubmit
     * @param null $cancelText
     * @param null $cancelCallback
     */
    public function __construct($text, $submitText, $callback, $autoSubmit, $cancelText = null, $cancelCallback = null)
    {
        $this->text = $text;
        $this->submitText = $submitText;
        $this->callback = $callback;
        $this->autoSubmit = $autoSubmit;
        $this->cancelText = $cancelText;
        $this->cancelCallback = $cancelCallback;
    }

    public function addElement(BaseElement $element)
    {
        $this->elements[] = $element;
    }
}
