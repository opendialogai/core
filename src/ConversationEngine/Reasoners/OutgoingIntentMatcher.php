<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\Core\Conversation\Intent;

class OutgoingIntentMatcher
{
    public static function matchOutgoingIntent(): Intent
    {
        return Intent::createIntent('intent.core.Response', 1);
    }
}
