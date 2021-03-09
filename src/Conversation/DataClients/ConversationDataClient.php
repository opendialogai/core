<?php


namespace OpenDialogAi\Core\Conversation\DataClients;

use Illuminate\Support\Facades\Http;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

/**
 * Draft Conversation Client
 */
class ConversationDataClient
{

    protected GraphQLClientInterface $client;

    public function __construct(GraphQLClientInterface $client)
    {
        $this->client = $client;
    }

    public function exampleGQLQuery()
    {
        return <<<'GQL'
            query Scenarios {
              queryScenario {
               name
               conversations {
                 name
               }
             }
        GQL;

    }

    public function query()
    {
        return $this->client->query($this->exampleGQLQuery());
    }

    /**
     * Retrieve all scenarios where active is set to true and their status is live
     * @param bool $shallow
     * @return ScenarioCollection
     * @todo handle returning scenarios that are in preview mode (or do we use an OD condition for that)
     */
    public function getAllActiveScenarios(bool $shallow): ScenarioCollection
    {
        return new ScenarioCollection();
    }

    /**
     * Retrieves a specific scenario
     *
     * @param string $scenarioId
     * @param bool $shallow
     * @return Scenario
     */
    public function getScenario(string $scenarioId, bool $shallow): Scenario
    {
        return new Scenario();
    }

    /**
     * Retrieve all conversations that belong to the given scenarios that have a behavior as "starting". from the graph
     *
     * @param ScenarioCollection $scenarios
     * @param bool $shallow
     * @return ConversationCollection
     */
    public function getAllStartingConversations(ScenarioCollection $scenarios, bool $shallow): ConversationCollection
    {
        return new ConversationCollection();
    }

    /**
     * Retrieve all conversations that belong to the given scenarios that have a behavior as "open". from the graph
     *
     * @param ScenarioCollection $scenarios
     * @param bool $shallow
     * @return ConversationCollection
     */
    public function getAllOpenConversations(ScenarioCollection $scenarios, bool $shallow): ConversationCollection
    {
        return new ConversationCollection();
    }

    /**
     * Retrieve all conversations that belong to the given scenarios. from the graph
     *
     * @param ScenarioCollection $scenarios
     * @param bool $shallow
     * @return ConversationCollection
     */
    public function getAllConversations(ScenarioCollection $scenarios, bool $shallow): ConversationCollection
    {
        return new ConversationCollection();
    }

    /**
     * Retrieves a specific conversation
     *
     * @param ScenarioCollection $scenarios
     * @param string $conversationId
     * @param bool $shallow
     * @return Conversation
     */
    public function getConversationById(ScenarioCollection $scenarios, string $conversationId, bool $shallow): Conversation
    {
        return new Conversation();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "starting" from the graph
     *
     * @param ConversationCollection $conversations
     * @param bool $shallow
     * @return SceneCollection
     */
    public function getAllStartingScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "open" from the graph
     *
     * @param ConversationCollection $conversations
     * @param bool $shallow
     * @return SceneCollection
     */
    public function getAllOpenScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations from the graph
     *
     * @param ConversationCollection $conversations
     * @param bool $shallow
     * @return SceneCollection
     */
    public function getAllScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * Retrieves a specific scene
     *
     * @param ConversationCollection $conversations
     * @param string $sceneId
     * @param bool $shallow
     * @return Scene
     */
    public function getSceneById(ConversationCollection $conversations, string $sceneId, bool $shallow): Scene
    {
        return new Scene();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "starting" from the graph
     *
     * @param SceneCollection $scenes
     * @param bool $shallow
     * @return TurnCollection
     */
    public function getAllStartingTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "open" from the graph
     *
     * @param SceneCollection $scenes
     * @param bool $shallow
     * @return TurnCollection
     */
    public function getAllOpenTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations from the graph
     *
     * @param SceneCollection $scenes
     * @param bool $shallow
     * @return TurnCollection
     */
    public function getAllTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     * Retrieves a specific turn
     *
     * @param SceneCollection $scenes
     * @param string $turnId
     * @param bool $shallow
     * @return Turn
     */
    public function getTurnById(SceneCollection $scenes, string $turnId, bool $shallow): Turn
    {
        return new Turn();
    }

    /**
     * Retrieve all request intents that belong to the given turns from the graph
     *
     * @param TurnCollection $turns
     * @param bool $shallow
     * @return IntentCollection
     */
    public function getAllRequestIntents(TurnCollection $turns, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * Retrieve all response intents that belong to the given turns from the graph
     *
     * @param TurnCollection $turns
     * @param bool $shallow
     * @return IntentCollection
     */
    public function getAllResponseIntents(TurnCollection $turns, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * Retrieve all request intents with the given ID that belong to the given turns from the graph
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $shallow
     * @return IntentCollection
     */
    public function getAllRequestIntentsById(TurnCollection $turns, string $intentId, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * Retrieve all response intents with the given ID that belong to the given turns from the graph
     *
     * @param TurnCollection $turns
     * @param string $intentId
     * @param bool $shallow
     * @return IntentCollection
     */
    public function getAllResponseIntentsById(TurnCollection $turns, string $intentId, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }
}
