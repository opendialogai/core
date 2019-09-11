<?php

namespace OpenDialogAi\Core\Utterances\Webchat;

use OpenDialogAi\Core\Utterances\FormResponseUtterance;
use OpenDialogAi\ResponseEngine\Message\Message;

class WebchatFormResponseUtterance extends FormResponseUtterance
{
    const PLATFORM = 'webchat';

    /**
     * Sets form values avoiding date and time
     * @param array $formValues
     */
    public function setFormValues(array $formValues): void
    {
        foreach ($formValues as $name => $value) {
            if (!in_array($name, [Message::DATE, Message::TIME, 'text'])) {
                $this->formValues[$name] = $value;
            }
        }
    }
}
