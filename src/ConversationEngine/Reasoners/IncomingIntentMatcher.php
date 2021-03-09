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
use OpenDialogAi\ConversationEngine\Util\ConversationContextUtil;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;

class IncomingIntentMatcher
{
    /**
     * Returns a single matching incoming intent, determined by the utterance and conversation context. Depending on
     * whether the user is in an ongoing conversation, and whether the user is in a turn request or turn response, will
     * determine how the matching is performed.
     *
     * @return Intent
     * @throws NoMatchingIntentsException
     */
    public static function matchIncomingIntent(): Intent
    {
        try {
            if (ConversationContextUtil::currentConversationId() == Conversation::UNDEFINED) {
                // It's a non-ongoing conversation
                return self::asStartingRequestIntent();
            } else {
                // It's an ongoing conversation
                if (ConversationContextUtil::currentIntentIsRequest()) {
                    // if "current" intent (at this point the current data is actually the previous data) is a request it
                    // means we previously dealt with a request
                    return self::asResponseIntent();
                } else {
                    return self::asRequestIntent();
                }
            }
        } catch (EmptyCollectionException $e) {
            Log::debug('No incoming intent selected');
            throw new NoMatchingIntentsException();
        }
    }

    /**
     * @return Intent
     * @throws EmptyCollectionException
     */
    private static function asRequestIntent(): Intent
    {
        $scenario = ScenarioSelector::selectScenarioById(ConversationContextUtil::currentScenarioId(), true);

        $conversation = ConversationSelector::selectConversationById(
            new ScenarioCollection([$scenario]),
            ConversationContextUtil::currentConversationId()
        );

        $scene = SceneSelector::selectSceneById(
            new ConversationCollection([$conversation]),
            ConversationContextUtil::currentSceneId()
        );

        $openTurns = TurnSelector::selectOpenTurns(new SceneCollection([$scene]));
        $turnsWithMatchingValidOrigin = TurnSelector::selectTurnsByValidOrigin(
            new SceneCollection([$scene]),
            ConversationContextUtil::currentIntentId()
        );

        $turns = $openTurns->concat($turnsWithMatchingValidOrigin);
        $turns = $turns->unique(function (Turn $turn) {
            return $turn->getODId();
        });

        $intents = IntentSelector::selectRequestIntents($turns, true, false);

        return IntentRanker::getTopRankingIntent($intents);
    }

    /**
     * @return Intent
     * @throws EmptyCollectionException
     */
    private static function asResponseIntent(): Intent
    {
        $scenario = ScenarioSelector::selectScenarioById(ConversationContextUtil::currentScenarioId(), true);

        $conversation = ConversationSelector::selectConversationById(
            new ScenarioCollection([$scenario]),
            ConversationContextUtil::currentConversationId()
        );

        $scene = SceneSelector::selectSceneById(
            new ConversationCollection([$conversation]),
            ConversationContextUtil::currentSceneId()
        );

        $turn = TurnSelector::selectTurnById(
            new SceneCollection([$scene]),
            ConversationContextUtil::currentTurnId()
        );

        $intents = IntentSelector::selectResponseIntents(new TurnCollection([$turn]), true, false);

        return IntentRanker::getTopRankingIntent($intents);
    }

    /**
     * @return Intent
     * @throws EmptyCollectionException
     */
    protected static function asStartingRequestIntent(): Intent
    {
        $currentScenarioId = ConversationContextUtil::currentScenarioId();
        $scenarios = new ScenarioCollection();

        if ($currentScenarioId == Scenario::UNDEFINED) {
            $scenarios = ScenarioSelector::selectScenarios(true);
        } else {
            $scenario = ScenarioSelector::selectScenarioById($currentScenarioId, true);
            $scenarios->addObject($scenario);
        }

        // Select valid conversations out of those scenarios - valid conversations will have the "starting" behavior
        // and their conditions will pass.
        $conversations = ConversationSelector::selectStartingConversations($scenarios);

        // Select valid scenes out of the opening conversations - valid scenes will have the "starting" behavior
        // and their conditions will pass
        $scenes = SceneSelector::selectStartingScenes($conversations);

        // Select valid turns out of the opening conversations - valid turns will have the "starting"
        $turns = TurnSelector::selectStartingTurns($scenes);

        // Select valid intents out of the valid turns. Valid intents will match the interpretation and have passing
        // conditions.
        $intents = IntentSelector::selectRequestIntents($turns, true, false);

        // Finally out of all the matching intents select the one with the highest confidence.
        return IntentRanker::getTopRankingIntent($intents);
    }
}
