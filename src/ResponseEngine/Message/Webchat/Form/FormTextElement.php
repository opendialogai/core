<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Form;

class FormTextElement extends FormElement
{
    /**
     * @return array
     */
    public function getData()
    {
        return parent::getData() + [
            'element_type' => 'text'
        ];
    }
}
