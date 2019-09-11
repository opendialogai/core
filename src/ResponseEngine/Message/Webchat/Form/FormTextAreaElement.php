<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Form;

class FormTextAreaElement extends FormElement
{
    /**
     * @return array
     */
    public function getData()
    {
        return parent::getData() + [
            'element_type' => 'textarea'
        ];
    }
}
