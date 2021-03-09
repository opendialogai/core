<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Util\ConversationContextUtil;
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
        if (ConversationContextUtil::currentConversationId() == Conversation::UNDEFINED) {
            // If there is no defined conversation we need to select an opening intent
            return OpeningIntentSelectorStrategy::selectOpeningIntent();
        } else {
            // Instead if we do have a conversation then we need to match to a request intent from within the conversation
            return MatchRequestIntentStartingFromConversationStrategy::matchRequestIntent(
                ConversationContextUtil::currentScenarioId(),
                ConversationContextUtil::currentConversationId(),
                ConversationContextUtil::currentTurnId(),
                ConversationContextUtil::currentIntentId()
            );
        }
    }
}
