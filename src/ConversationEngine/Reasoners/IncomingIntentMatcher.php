<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Util\MatcherUtil;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;

class IncomingIntentMatcher
{
    /**
     * @return Intent
     * @throws NoMatchingIntentsException
     */
    public static function matchIncomingIntent(): Intent
    {
        if (MatcherUtil::currentConversationId() == Conversation::UNDEFINED) {
            // If there is no defined conversation we need to select an opening intent
            return OpeningIntentSelectorStrategy::selectOpeningIntent();
        } else {
            // Instead if we do have a conversation then we need to match to a request intent from within the conversation
            return MatchRequestIntentStartingFromConversationStrategy::matchRequestIntent(
                MatcherUtil::currentScenarioId(),
                MatcherUtil::currentConversationId(),
                MatcherUtil::currentTurnId(),
                MatcherUtil::currentIntentId()
            );
        }
    }
}
