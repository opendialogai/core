<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;

class IntentRanker
{
    /**
     * @param IntentCollection $intents
     * @return Intent
     * @throws EmptyCollectionException
     */
    public static function getTopRankingIntent(IntentCollection $intents): Intent
    {
        return Intent::createIntent('', 1);
    }
}
