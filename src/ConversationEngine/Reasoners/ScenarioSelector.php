<?php


namespace OpenDialogAi\ConversationEngine\Reasoners;

use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
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

}
