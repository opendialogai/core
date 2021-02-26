<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Util\MatcherUtil;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;

class IncomingIntentMatcher
{
    /**
     * @return Intent
     * @throws NoMatchingIntentsException
     */
    public static function matchIncomingIntent(): Intent
    {
        // If there is no defined scenario or there is no defined conversation we need to select an opening intent
        if (MatcherUtil::currentScenarioId() == Scenario::UNDEFINED
            || MatcherUtil::currentConversationId() == Conversation::UNDEFINED) {
            return OpeningIntentSelectorStrategy::selectOpeningIntent();
        }

        // Instead if we do have a conversation then we need to match to a request intent from within the conversation
        if (MatcherUtil::currentConversationId() != Conversation::UNDEFINED) {
            return MatchRequestIntentStartingFromConversationStrategy::matchRequestIntent(
                MatcherUtil::currentScenarioId(),
                MatcherUtil::currentConversationId(),
                MatcherUtil::currentTurnId(),
                MatcherUtil::currentIntentId()
            );
        }

        throw new NoMatchingIntentsException();
    }
}
