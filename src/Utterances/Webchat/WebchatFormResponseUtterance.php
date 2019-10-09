<?php

namespace OpenDialogAi\Core\Utterances\Webchat;

use OpenDialogAi\Core\Utterances\FormResponseUtterance;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebchatTextMessage;

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
            if (!in_array($name, [WebchatTextMessage::DATE, WebchatTextMessage::TIME, 'text'])) {
                $this->formValues[$name] = $value;
            }
        }
    }
}
