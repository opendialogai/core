<?php


namespace OpenDialogAi\Core\Conversation\DataClients;

use Carbon\Carbon;
use Mockery\Exception;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

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

    public function query()
    {
        return $this->client->query($this->exampleGQLQuery());
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
            }
        GQL;

    }

    /**
     * Retrieve all scenarios where active is set to true and their status is live
     *
     * @param  bool  $shallow
     *
     * @return ScenarioCollection
     * @todo handle returning scenarios that are in preview mode (or do we use an OD condition for that)
     */
    public function getAllActiveScenarios(bool $shallow): ScenarioCollection {
        $getAllActiveScenariosQuery = <<<'GQL'
            query getAllActiveScenarios {
                queryScenario(filter:  {active: true, status:{ eq: LIVE }}) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    active
                    status
                    created_at
                    updated_at
                }
            }
        GQL;

        $response = $this->client->query($getAllActiveScenariosQuery);
        $serializer = new Serializer([
            new ScenarioCollectionNormalizer(), new ScenarioNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer()
        ], [
            new
            JsonEncoder()
        ]);
        return $serializer->denormalize($response['data']['queryScenario'], ScenarioCollection::class);
    }
    public function getAllScenarios(bool $shallow): ScenarioCollection
    {
        $getAllScenariosQuery = <<<'GQL'
            query getAllScenarios {
                queryScenario {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    active
                    status
                    created_at
                    updated_at
                }
            }
        GQL;

        $response = $this->client->query($getAllScenariosQuery);
        $serializer = new Serializer([
            new ScenarioCollectionNormalizer(), new ScenarioNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer()
        ], [
            new
            JsonEncoder()
        ]);
        return $serializer->denormalize($response['data']['queryScenario'], ScenarioCollection::class);
    }

    public function getScenarioByUid(string $scenarioUid, bool $shallow): ?Scenario
    {
        $getScenarioQuery = <<<'GQL'
            query getScenario($id : ID!) {
                getScenario(id: $id) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    active
                    status
                    created_at
                    updated_at
                }
            }
        GQL;

        $response = $this->client->query($getScenarioQuery, ['id' => $scenarioUid]);
        if($response['data']['getScenario'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Scenario with uid %s could not be found',
                $scenarioUid));
        }

        $serializer = new Serializer([
            new ScenarioNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer()
        ], [
            new
            JsonEncoder()
        ]);
        return  $serializer->denormalize($response['data']['getScenario'], Scenario::class);
    }

    public function addScenario(Scenario $scenario): Scenario
    {
        $addScenarioQuery = <<<'GQL'
            mutation addScenarioQuery($scenario: AddScenarioInput!) {
              addScenario(input: [$scenario]) {
                scenario {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    active
                    status
                    created_at
                    updated_at
                }
              }
            }
        GQL;

        $scenario->setCreatedAt(Carbon::now());
        $scenario->setUpdatedAt(Carbon::now());

        $missing = array_filter([Scenario::OD_ID, Scenario::NAME, Scenario::ACTIVE, Scenario::STATUS], fn($required) =>
        !in_array($required, $scenario->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields %s are missing from the scenario supplied to ConversationDataClient::addScenario(), but are required!", implode(",", $missing)));
        }

        $serializer = new Serializer([
            new ScenarioNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer()
        ], []);
        //TODO: Update to allow entering a full scenario graph
        $scenarioData = $serializer->normalize($scenario, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                Scenario::OD_ID, Scenario::NAME, Scenario::DESCRIPTION, Scenario::INTERPRETER, Scenario::ACTIVE, Scenario::STATUS,
                Scenario::CREATED_AT, Scenario::UPDATED_AT
                // TODO: Reintroduce conditions
                //        , Scenario::CONDITIONS
                , Scenario::BEHAVIORS => Behavior::FIELDS
            ]
        ]);

        $response = $this->client->query($addScenarioQuery, ['scenario' => $scenarioData]);
        return $serializer->denormalize($response['data']['addScenario']['scenario'][0], Scenario::class);
    }

    public function deleteScenarioByUid(string $scenarioUid): bool
    {
        $deleteScenarioQuery = <<<'GQL'
            mutation deleteScenarioQuery($id: ID!) {
                deleteScenario(filter: {id: [$id]}) {
                    scenario {
                        id
                    }
                }
            }
        GQL;

        $response = $this->client->query($deleteScenarioQuery, ['id' => $scenarioUid]);
        // Is this neccesary? We could just not care.
        if(empty($response['data']['deleteScenario']['scenario'])) {
            throw new ConversationObjectNotFoundException(sprintf('Scenario with uid %s could not be found',
                $scenarioUid));
        }
        return true;
    }


    public function updateScenario(Scenario $scenario): ?Scenario
    {
        $updateScenarioQuery = <<<'GQL'
            mutation updateScenarioQuery($id: ID!, $set: ScenarioPatch!) {
                updateScenario(input: {filter: {id: [$id]}, set: $set}) {
                    scenario {
                        id
                        od_id
                        name
                        description
                        interpreter
                        behaviors
                        conditions {
                            id
                        }
                        active
                        status
                        created_at
                        updated_at
                    }
                }
            }
        GQL;

        $missing = array_filter([Scenario::UID], fn($required) =>
        !in_array($required, $scenario->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the scenario supplied to ConversationDataClient::updateScenario(), but are required!", implode(",", $missing)));
        }

        $scenario->setUpdatedAt(Carbon::now());

        $serializer = new Serializer([
            new ScenarioNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer()
        ], []);
        //TODO: Update to allow entering a full scenario graph

        // Remove UID from patch fields. We can't change the UID
        $patchFields = array_diff($scenario->hydratedFields(), [Scenario::UID]);

        $serializationTree = ScenarioNormalizer::filterSerializationTree(ScenarioNormalizer::fullExpansion(),
            $patchFields);
        $scenarioData = $serializer->normalize($scenario, 'json', [AbstractNormalizer::ATTRIBUTES => $serializationTree]);
        $response = $this->client->query($updateScenarioQuery, ['id' => $scenario->getUid(), 'set' => $scenarioData]);
        return $serializer->denormalize($response['data']['updateScenario']['scenario'][0], Scenario::class);

    }

//    /**
//     * Retrieve all scenarios where active is set to true and their status is live
//     *
//     * @param  bool  $shallow
//     *
//     * @return ScenarioCollection
//     * @todo handle returning scenarios that are in preview mode (or do we use an OD condition for that)
//     */
//    public function getAllActiveScenarios(bool $shallow): ScenarioCollection
//    {
//        return new ScenarioCollection();
//    }


    /**
     * Retrieve all conversations that belong to the given scenarios that have a behavior as "starting". from the graph
     *
     * @param  ScenarioCollection  $scenarios
     * @param  bool                $shallow
     *
     * @return ConversationCollection
     */
    public function getAllStartingConversations(ScenarioCollection $scenarios, bool $shallow): ConversationCollection
    {
        return new ConversationCollection();
    }

    /**
     * Retrieve all conversations that belong to the given scenarios that have a behavior as "open". from the graph
     *
     * @param  ScenarioCollection  $scenarios
     * @param  bool                $shallow
     *
     * @return ConversationCollection
     */
    public function getAllOpenConversations(ScenarioCollection $scenarios, bool $shallow): ConversationCollection
    {
        return new ConversationCollection();
    }

    /**
     * Retrieve all conversations that belong to the given scenarios. from the graph
     *
     * @param  ScenarioCollection  $scenarios
     * @param  bool                $shallow
     *
     * @return ConversationCollection
     */
    public function getAllConversations(ScenarioCollection $scenarios, bool $shallow): ConversationCollection
    {
        return new ConversationCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "starting" from the graph
     *
     * @param  ConversationCollection  $conversations
     * @param  bool                    $shallow
     *
     * @return SceneCollection
     */
    public function getAllStartingScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "open" from the graph
     *
     * @param  ConversationCollection  $conversations
     * @param  bool                    $shallow
     *
     * @return SceneCollection
     */
    public function getAllOpenScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations from the graph
     *
     * @param  ConversationCollection  $conversations
     * @param  bool                    $shallow
     *
     * @return SceneCollection
     */
    public function getAllScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        return new SceneCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "starting" from the graph
     *
     * @param  SceneCollection  $scenes
     * @param  bool             $shallow
     *
     * @return TurnCollection
     */
    public function getAllStartingTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "open" from the graph
     *
     * @param  SceneCollection  $scenes
     * @param  bool             $shallow
     *
     * @return TurnCollection
     */
    public function getAllOpenTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     * Retrieve all scenes that belong to the given conversations from the graph
     *
     * @param  SceneCollection  $scenes
     * @param  bool             $shallow
     *
     * @return TurnCollection
     */
    public function getAllTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        return new TurnCollection();
    }

    /**
     * Retrieve all request intents that belong to the given turns from the graph
     *
     * @param  TurnCollection  $turns
     * @param  bool            $shallow
     *
     * @return IntentCollection
     */
    public function getAllRequestIntents(TurnCollection $turns, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * Retrieve all response intents that belong to the given turns from the graph
     *
     * @param  TurnCollection  $turns
     * @param  bool            $shallow
     *
     * @return IntentCollection
     */
    public function getAllResponseIntents(TurnCollection $turns, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * Retrieve all request intents with the given ID that belong to the given turns from the graph
     *
     * @param  TurnCollection  $turns
     * @param  string          $intentId
     * @param  bool            $shallow
     *
     * @return IntentCollection
     */
    public function getAllRequestIntentsById(TurnCollection $turns, string $intentId, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }

    /**
     * Retrieve all response intents with the given ID that belong to the given turns from the graph
     *
     * @param  TurnCollection  $turns
     * @param  string          $intentId
     * @param  bool            $shallow
     *
     * @return IntentCollection
     */
    public function getAllResponseIntentsById(TurnCollection $turns, string $intentId, bool $shallow): IntentCollection
    {
        return new IntentCollection();
    }
}
