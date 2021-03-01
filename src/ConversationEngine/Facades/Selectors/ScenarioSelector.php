<?php

namespace OpenDialogAi\ConversationEngine\Facades\Selectors;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;

/**
 * @method static ScenarioCollection selectScenarios(bool $shallow = true)
 * @method static Scenario selectScenarioById(string $scenarioId, bool $shallow = true)
 */
class ScenarioSelector extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\ConversationEngine\Selectors\ScenarioSelector::class;
    }
}
