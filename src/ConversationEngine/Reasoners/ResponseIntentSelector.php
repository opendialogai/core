<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;

class ResponseIntentSelector
{
    public static function getResponseIntentForRequestIntent(Intent $requestIntent): Intent
    {
        return Intent::createIntent('intent.core.Response', 1);
    }
}
