<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * The ScenarioSelector should use the User context and evaluate conditions against scenarios to select
 * which scenarios can validly be considered for the current user
 */
class ScenarioSelector
{
    public static function selectActiveScenarios(): ScenarioCollection
    {
        $scenarios = ConversationDataClient::getAllActiveScenarios();

        $conditionPassingScenarios = $scenarios->filter(function ($scenario) {
            return ConditionFilter::checkConditions($scenario);
        });
        return $conditionPassingScenarios;
    }

}
