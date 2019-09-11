<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Form;

class FormNumberElement extends FormElement
{
    /**
     * @return array
     */
    public function getData()
    {
        return parent::getData() + [
            'element_type' => 'number'
        ];
    }
}
