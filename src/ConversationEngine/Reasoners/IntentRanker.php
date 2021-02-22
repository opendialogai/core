<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;

class IntentRanker
{
    public static function getTopRankingIntent(IntentCollection $intents): Intent
    {
        return Intent::createIntent('', 1);
    }
}
