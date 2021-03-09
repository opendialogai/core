<?php


namespace OpenDialogAi\ConversationEngine\Selectors;

use OpenDialogAi\ConversationEngine\Reasoners\ConditionFilter;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * The ScenarioSelector should use the User context and evaluate conditions against scenarios to select
 * which scenarios can validly be considered for the current user
 */
class ScenarioSelector
{
    /**
     * Retrieves all scenarios
     *
     * @param bool $shallow
     * @return ScenarioCollection
     */
    public static function selectScenarios(bool $shallow = true): ScenarioCollection
    {
        $scenarios = ConversationDataClient::getAllActiveScenarios($shallow);

        /** @var ScenarioCollection $scenariosWithPassingConditions */
        $scenariosWithPassingConditions = ConditionFilter::filterObjects($scenarios);

        return $scenariosWithPassingConditions;
    }

    /**
     * Retrieves a specific scenario
     *
     * @param string $scenarioId
     * @param bool $shallow
     * @return Scenario
     */
    public static function selectScenarioById(string $scenarioId, bool $shallow = true): Scenario
    {
        return ConversationDataClient::getScenarioById($scenarioId, $shallow);
    }
}
