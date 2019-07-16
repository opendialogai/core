<?php

namespace OpenDialogAi\Core\Utterances\Webchat;

use OpenDialogAi\Core\Utterances\FormResponseUtterance;
use OpenDialogAi\ResponseEngine\Message\Webchat\WebChatMessage;

class WebchatFormResponseUtterance extends FormResponseUtterance
{
    const PLATFORM = 'webchat';

    const TYPE = 'webchat_form_response';

    /**
     * Sets form values avoiding date and time
     * @param array $formValues
     */
    public function setFormValues(array $formValues): void
    {
        foreach ($formValues as $name => $value) {
            if (!in_array($name, [WebChatMessage::DATE, WebChatMessage::TIME, 'callback_id'])) {
                $this->formValues[$name] = $value;
            }
        }
    }
}
