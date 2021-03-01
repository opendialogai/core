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
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * The OpeningIntentSelector drills down from Scenarios to Intents to find an appropriate opening
 * intent.
 */
class OpeningIntentSelectorStrategy
{
    /**
     * @return Intent
     * @throws NoMatchingIntentsException
     */
    public static function selectOpeningIntent(): Intent
    {
        $currentScenarioId = MatcherUtil::currentScenarioId();
        $scenarios = new ScenarioCollection();

        if ($currentScenarioId == Scenario::UNDEFINED) {
            $scenarios = ScenarioSelector::selectScenarios(true);
        } else {
            $scenario = ScenarioSelector::selectScenarioById($currentScenarioId, true);
            $scenarios->addObject($scenario);
        }

        try {
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
            $intents = IntentSelector::selectRequestIntents($turns);

            // Finally out of all the matching intents select the one with the highest confidence.
            return IntentRanker::getTopRankingIntent($intents);
        } catch (EmptyCollectionException $e) {
            Log::debug('No opening intent selected');
            throw new NoMatchingIntentsException();
        }
    }

}
