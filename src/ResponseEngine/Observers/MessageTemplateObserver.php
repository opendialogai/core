<?php

namespace OpenDialogAi\ResponseEngine\Observers;

use OpenDialogAi\ResponseEngine\MessageTemplate;

class MessageTemplateObserver
{
    /**
     * Handle the message template "saving" event.
     *
     * @param  MessageTemplate  $messageTemplate
     * @return void
     */
    public function saving(MessageTemplate $messageTemplate)
    {
        $messageTemplate->version_number++;
    }
}
