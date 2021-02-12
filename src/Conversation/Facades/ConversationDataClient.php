<?php
namespace OpenDialogAi\Core\Conversation\Facades;

use Illuminate\Support\Facades\Facade;

class ConversationDataClient extends Facade
{
    /**
     * @method static function getAllActiveScenarios(): ScenarioCollection
     * @method static function getAllStartingConversations(ScenarioCollection $scenarios): ConversationCollection
     * @method static function getAllStartingScenes(ConversationCollection $conversations): SceneCollection
     * @method static function getAllStartingTurns(SceneCollection $scenes): TurnCollection
     * @method static function getAllStartingIntents(TurnCollection $turns) IntentCollection
     * @method static function getShallowScenario($scenario_id): Scenario
     **/
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\Core\Conversation\DataClients\ConversationDataClient::class;
    }
}
