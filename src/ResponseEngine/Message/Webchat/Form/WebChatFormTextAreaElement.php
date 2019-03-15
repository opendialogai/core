<?php

namespace OpenDialogAi\ResponseEngine\Message\Webchat\Form;

class WebChatFormTextAreaElement extends WebChatFormElement
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
