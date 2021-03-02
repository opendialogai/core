<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * The OpeningIntentSelector drills down from Scenarios to Intents to find an appropriate opening
 * intent.
 */
class OpeningIntentSelectorStrategy
{
    public static function selectOpeningIntent(): Intent
    {
        $current_scenario_id = ContextService::getAttribute(Scenario::CURRENT_SCENARIO,
                ConversationEngine::CONVERSATION_CONTEXT);
        $scenarios = new ScenarioCollection();

        if ($current_scenario_id == Scenario::UNDEFINED) {
            // Select valid scenarios based on whether they have passing conditions
            /* @var ScenarioCollection $scenarios */
            $scenarios = ScenarioSelector::selectActiveScenarios();
        } else {
            $scenario = ConversationDataClient::getShallowScenario($current_scenario_id);
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
        $intents = StartingIntentSelector::selectStartingIntents($turns);

        try {
            // Finally out of all the matching intents select the one with the highest confidence.
            return IntentRanker::getTopRankingIntent($intents);
        } catch (EmptyCollectionException $e) {
            return Intent::createNoMatchIntent();
        }
    }

}
