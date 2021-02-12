<?php


namespace OpenDialogAi\Core\Conversation\DataClients;

use Illuminate\Support\Facades\Http;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;

/**
 * Draft Conversation Client
 */
class ConversationDataClient
{
    protected string $url;
    protected string $port;
    protected string $api;

    public function __construct($url, $port, $api)
    {
        $this->url = $url;
        $this->port = $port;
        $this->api = $api;
    }

    public function exampleGQLQuery()
    {
        return $array =  [
            "query" => "
query Scenarios {
  queryScenario {
   name
   conversations {
     name
   }
 }
}",
        ];

    }

    public function query()
    {
        $response = Http::baseUrl($this->url)
            ->post('graphql',
                $this->exampleGQLQuery());

        return($response->json());
    }

    /**
     * Retrieve all scenarios where active is set to true and their status is live
     * @todo handle returning scenarios that are in preview mode (or do we use an OD condition for that)
     * @return ScenarioCollection
     */
    public function getAllActiveScenarios(): ScenarioCollection
    {
        return new ScenarioCollection();
    }

    /**
     * Retrieve all conversations from within the scenario collection that have a behavior as "starting".
     * @return ConversationCollection
     */
    public function getAllStartingConversations(ScenarioCollection $scenarios): ConversationCollection
    {
        return new ConversationCollection();
    }

    /**
     * Retrieve all scenes from within the conversation collection that have a behavior as "starting"
     * @param ConversationCollection $conversations
     * @return SceneCollection
     */
    public function getAllStartingScenes(ConversationCollection $conversations): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * @param SceneCollection $scenes
     * @return TurnCollection
     */
    public function getAllStartingTurns(SceneCollection $scenes): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     * @param TurnCollection $turns
     * @return TurnCollection
     */
    public function getAllStartingIntents(TurnCollection $turns): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * @param $scenario_id
     * @return Scenario
     */
    public function getShallowScenario($scenario_id): Scenario
    {
        return new Scenario();
    }
}
