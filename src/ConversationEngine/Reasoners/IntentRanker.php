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
        if ($intents->isEmpty()) {
            throw new EmptyCollectionException();
        }

        if (count($intents) < 2) {
            return $intents->first();
        }

        /** @var Intent $topRankingIntent */
        $topRankingIntent = null;

        /** @var Intent $intent */
        foreach ($intents as $intent) {
            if (is_null($topRankingIntent) || self::isIntentHigherRanking($intent, $topRankingIntent)) {
                $topRankingIntent = $intent;
            }
        }

        return $topRankingIntent;
    }

    /**
     * Returns whether intent A is higher ranking than intent B, based first off of confidence, then number of attributes
     *
     * @param Intent $a
     * @param Intent $b
     * @return bool
     */
    private static function isIntentHigherRanking(Intent $a, Intent $b): bool
    {
        $confidenceComparison = $a->getInterpretation()->getConfidence() <=> $b->getInterpretation()->getConfidence();

        if ($confidenceComparison === 0) {
            // If the confidences were the same judge by number of (matching expected) attributes
            return count($a->getInterpretation()->getAttributes()) > count($b->getInterpretation()->getAttributes());
        } else {
            // If the confidences were different the judge by whether it was higher or lower
            return $confidenceComparison > 0;
        }
    }
}
