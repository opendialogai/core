<?php

namespace OpenDialogAi\MessageBuilder\Message;

class FullPageFormMessage extends BaseFormMessage
{
    public function getMarkUp()
    {
        $elementMarkup = '';

        foreach ($this->elements as $element) {
            $elementMarkup .= $element->getMarkUp();
        }

        return <<<EOT
<fp-form-message>
    <text>$this->text</text>
    <submit_text>$this->submitText</submit_text>
    <callback>$this->callback</callback>
    <auto_submit>$this->autoSubmit</auto_submit>
    $elementMarkup
</fp-form-message>
EOT;
    }
}
