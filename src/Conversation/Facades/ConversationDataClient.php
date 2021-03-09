<?php
namespace OpenDialogAi\Core\Conversation\Facades;

use Illuminate\Support\Facades\Facade;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;

/**
 * @method static ScenarioCollection getAllActiveScenarios(bool $shallow)
 * @method static Scenario getScenarioById(string $scenarioId, bool $shallow)
 * @method static ConversationCollection getAllStartingConversations(ScenarioCollection $scenarios, bool $shallow)
 * @method static ConversationCollection getAllOpenConversations(ScenarioCollection $scenarios, bool $shallow)
 * @method static ConversationCollection getAllConversations(ScenarioCollection $scenarios, bool $shallow)
 * @method static Conversation getConversationById(ScenarioCollection $scenarios, string $conversationId, bool $shallow)
 * @method static SceneCollection getAllStartingScenes(ConversationCollection $conversations, bool $shallow)
 * @method static SceneCollection getAllOpenScenes(ConversationCollection $conversations, bool $shallow)
 * @method static SceneCollection getAllScenes(ConversationCollection $conversations, bool $shallow)
 * @method static Scene getSceneById(ConversationCollection $conversations, string $sceneId, bool $shallow)
 * @method static TurnCollection getAllStartingTurns(SceneCollection $scenes, bool $shallow)
 * @method static TurnCollection getAllOpenTurns(SceneCollection $scenes, bool $shallow)
 * @method static TurnCollection getAllTurns(SceneCollection $scenes, bool $shallow)
 * @method static Turn getTurnById(SceneCollection $scenes, string $turnId, bool $shallow)
 * @method static IntentCollection getAllRequestIntents(TurnCollection $turns, bool $shallow)
 * @method static IntentCollection getAllResponseIntents(TurnCollection $turns, bool $shallow)
 * @method static IntentCollection getAllRequestIntentsById(TurnCollection $turns, string $intentId, bool $shallow)
 * @method static IntentCollection getAllResponseIntentsById(TurnCollection $turns, string $intentId, bool $shallow)
 **/
class ConversationDataClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \OpenDialogAi\Core\Conversation\DataClients\ConversationDataClient::class;
    }
}
