<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;


use Illuminate\Support\Facades\Log;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\ConversationEngine\Util\MatcherUtil;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;

class OutgoingIntentMatcher
{
    /**
     * Returns a single matching outgoing intent, determined by the conversation context. Depending on whether the user
     * is in an ongoing conversation, and whether the user is in a turn request or turn response, will determine how
     * the matching is performed.
     *
     * @return Intent
     * @throws NoMatchingIntentsException
     */
    public static function matchOutgoingIntent(): Intent
    {
        try {
            if (MatcherUtil::currentConversationId() == Conversation::UNDEFINED) {
                // Its a non-ongoing conversation
                return self::asStartingRequestIntent();
            } else {
                // Its an ongoing conversation
                if (MatcherUtil::currentIntentIsRequest()) {
                    // if "current" intent (at this point the current data is actually the previous data) is a request it
                    // means we previously dealt with a request
                    return self::asResponseIntent();
                } else {
                    return self::asRequestIntent();
                }
            }
        } catch (EmptyCollectionException $e) {
            Log::debug('No outgoing intent selected');
            throw new NoMatchingIntentsException();
        }
    }

    /**
     * @return Intent
     */
    private static function asStartingRequestIntent(): Intent
    {
        return new Intent();
    }

    /**
     * @return Intent
     */
    private static function asRequestIntent(): Intent
    {
        $scenario = ScenarioSelector::selectScenarioById(MatcherUtil::currentScenarioId(), true);

        $conversation = ConversationSelector::selectConversationById(
            new ScenarioCollection([$scenario]),
            MatcherUtil::currentConversationId()
        );

        $scene = SceneSelector::selectSceneById(
            new ConversationCollection([$conversation]),
            MatcherUtil::currentSceneId()
        );

        $openTurns = TurnSelector::selectOpenTurns(new SceneCollection([$scene]));
        $turnsWithMatchingValidOrigin = TurnSelector::selectTurnsByValidOrigin(
            new SceneCollection([$scene]),
            MatcherUtil::currentIntentId()
        );

        $turns = $openTurns->concat($turnsWithMatchingValidOrigin);
        $turns = $turns->unique(function (Turn $turn) {
            return $turn->getODId();
        });

        $intents = IntentSelector::selectResponseIntents($turns, false);

        return $intents->first();
    }

    /**
     * @return Intent
     * @throws NoMatchingIntentsException
     * @throws EmptyCollectionException
     */
    private static function asResponseIntent(): Intent
    {
        $scenario = ScenarioSelector::selectScenarioById(MatcherUtil::currentScenarioId(), true);

        $conversation = ConversationSelector::selectConversationById(
            new ScenarioCollection([$scenario]),
            MatcherUtil::currentConversationId(),
            true
        );

        $scene = SceneSelector::selectSceneById(
            new ConversationCollection([$conversation]),
            MatcherUtil::currentSceneId(),
            true
        );

        $turn = TurnSelector::selectTurnById(
            new SceneCollection([$scene]),
            MatcherUtil::currentTurnId(),
            true
        );

        $intents = IntentSelector::selectResponseIntents(new TurnCollection([$turn]), false);

        if ($intents->isEmpty()) {
            throw new NoMatchingIntentsException();
        }

        return $intents->first();
    }
}
