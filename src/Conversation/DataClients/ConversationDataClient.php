<?php


namespace OpenDialogAi\Core\Conversation\DataClients;

use Carbon\Carbon;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnNormalizer;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Helpers\SerializationTreeHelper;

/**
 * Draft Conversation Client
 */
class ConversationDataClient
{

    protected GraphQLClientInterface $client;

    public static function getNormalizers() {
        return [new ScenarioCollectionNormalizer(), new ScenarioNormalizer(), new
        ConversationCollectionNormalizer(), new ConversationNormalizer(), new SceneCollectionNormalizer(), new SceneNormalizer(),
            new TurnCollectionNormalizer(), new TurnNormalizer(), new IntentCollectionNormalizer(), new IntentNormalizer(), new
            ConditionCollectionNormalizer(), new ConditionNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer
            ()];
    }


    /**
     * @param $obj
     * @param $tree
     *
     * @return array
     */
    public function checkRequired(ConversationObject  $obj, $tree): array {
        $hydrated = $obj->hydratedFields();

    }

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
                    conversations {
                        id
                        od_id
                    }
                }
            }
        GQL;

        $response = $this->client->query($getAllActiveScenariosQuery);
        $serializer = new Serializer(self::getNormalizers(), []);
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
                    conversations {
                        id
                        od_id
                    }
                }
            }
        GQL;

        $response = $this->client->query($getAllScenariosQuery);
        $serializer = new Serializer(self::getNormalizers(), []);
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
                    conversations {
                        id
                        od_id
                    }
                }
            }
        GQL;

        $response = $this->client->query($getScenarioQuery, ['id' => $scenarioUid]);
        if($response['data']['getScenario'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Scenario with uid %s could not be found',
                $scenarioUid));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
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
                    conversations {
                        id
                    }
                }
              }
            }
        GQL;

        $scenario->setCreatedAt(Carbon::now());
        $scenario->setUpdatedAt(Carbon::now());

        $required = [
            Scenario::OD_ID,
            Scenario::NAME,
            Scenario::CREATED_AT,
            Scenario::UPDATED_AT,
            Scenario::ACTIVE,
            Scenario::STATUS
        ];
        $missing = array_diff($required, $scenario->hydratedFields());

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields %s are missing from the scenario supplied to ConversationDataClient::addScenario(), but are required!", implode(",", $missing)));
        }

        $maxTree = [
          Scenario::OD_ID,
          Scenario::NAME,
          Scenario::DESCRIPTION,
          Scenario::INTERPRETER,
          Scenario::CONDITIONS => Condition::FIELDS,
          Scenario::BEHAVIORS => Behavior::FIELDS,
          Scenario::STATUS,
          Scenario::ACTIVE,
          Scenario::CREATED_AT,
          Scenario::UPDATED_AT,
        ];

        $serializer = new Serializer(self::getNormalizers(), []);

        $hydrated = $scenario->hydratedFields();
        $tree = SerializationTreeHelper::filterSerializationTree($maxTree, $scenario->hydratedFields());
        $scenarioData = $serializer->normalize($scenario, 'json', [
            AbstractNormalizer::ATTRIBUTES => $tree
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

    public function updateScenario(Scenario $scenario): Scenario
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
                        conversations {
                            id
                        }
                    }
                }
            }
        GQL;
        $scenario->setUpdatedAt(Carbon::now());

        $missing = array_diff([Scenario::UID], $scenario->hydratedFields());
        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the scenario supplied to ConversationDataClient::updateScenario(), but are required!", implode(",", $missing)));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        // Remove UID from patch fields. We can't change the UID
        $patchFields = array_diff($scenario->hydratedFields(), [Scenario::UID]);
        $tree = array_merge(Scenario::localFields(),
            [Scenario::BEHAVIORS => Behavior::FIELDS, Scenario::CONDITIONS => Condition::FIELDS]);

        $serializationTree = SerializationTreeHelper::filterSerializationTree($tree, $patchFields);
        $scenarioData = $serializer->normalize($scenario, 'json', [AbstractNormalizer::ATTRIBUTES => $serializationTree]);
        $response = $this->client->query($updateScenarioQuery, ['id' => $scenario->getUid(), 'set' => $scenarioData]);
        return $serializer->denormalize($response['data']['updateScenario']['scenario'][0], Scenario::class);

    }

    /**
     * Adds a new conversation object.
     * The supplied conversation object must reference an existing Scenario (i.e one with a UID)
     *
     * @param  Conversation  $conversation
     *
     * @return Conversation
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function addConversation(Conversation $conversation): Conversation {
        $addConversationQuery = <<<'GQL'
            mutation addConversationQuery($conversation: AddConversationInput!) {
              addConversation(input: [$conversation]) {
                conversation {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scenario {
                        id
                    }
                    scenes {
                        id
                    }
                }
              }
            }
        GQL;

        $conversation->setCreatedAt(Carbon::now());
        $conversation->setUpdatedAt(Carbon::now());

        $missing = array_filter([
            Conversation::OD_ID,
            Conversation::NAME,
            Conversation::CREATED_AT,
            Conversation::UPDATED_AT,
            Conversation::SCENARIO
        ], fn
        ($required) =>
        !in_array($required, $conversation->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields (%s) are missing from the conversation supplied to ConversationDataClient::addConversation(), but are required!", implode(",", $missing)));
        }

        $missing = array_filter([Scenario::UID], fn($required) => !in_array($required, $conversation->getScenario()
            ->hydratedFields()));
        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields (%s) are missing from the scenario attached to the conversation supplied to ConversationDataClient::addConversation(), but are required!", implode(",", $missing)));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        //TODO: Update to allow entering a full conversation graph
        $tree = [Conversation::localFields()];
        $conversationData = $serializer->normalize($conversation, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                Conversation::OD_ID,
                Conversation::NAME,
                Conversation::DESCRIPTION,
                Conversation::INTERPRETER,
                Conversation::CREATED_AT,
                Conversation::UPDATED_AT,
                Conversation::BEHAVIORS => Behavior::FIELDS,
                Conversation::CONDITIONS => Condition::FIELDS,
                Conversation::SCENARIO => [Scenario::UID]
            ]
        ]);

        $response = $this->client->query($addConversationQuery, ['conversation' => $conversationData]);
        return $serializer->denormalize($response['data']['addConversation']['conversation'][0], Conversation::class);
    }

    /***
     * Retrive all conversations that belong to the given scenario
     */
    public function getAllConversationsByScenario(Scenario $scenario, bool $shallow): ConversationCollection {
        $getAllConversationsByScenarioQuery = <<<'GQL'
            query getAllConversationsByScenario($scenarioUid: ID!) {
                queryConversation @cascade(fields: ["scenario"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scenario(filter: {id: [$scenarioUid]}) {
                        id
                    }
                    scenes {
                        id
                    }
                }
            }
        GQL;

        $missing = array_filter([Scenario::UID], fn($required) =>
        !in_array($required, $scenario->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the scenario supplied to ConversationDataClient::getAllConversationsByScenario(), but are required!", implode(",", $missing)));
        }

        $response = $this->client->query($getAllConversationsByScenarioQuery, ['scenarioUid' => $scenario->getUid()]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryConversation'], ConversationCollection::class);
    }

    /**
     * Get Conversation with the provided uid
     * @param  string  $conversationUid
     * @param  bool    $shallow
     *
     * @return Conversation
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getConversationByUid(string $conversationUid, bool $shallow): Conversation {
        $getConversationQuery = <<<'GQL'
            query getConversation($id : ID!) {
                getConversation(id: $id) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scenario {
                        id
                    }
                    scenes {
                        id
                    }
                }
            }
        GQL;

        $response = $this->client->query($getConversationQuery, ['id' => $conversationUid]);
        if($response['data']['getConversation'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Conversation with uid %s could not be found',
                $conversationUid));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        return  $serializer->denormalize($response['data']['getConversation'], Conversation::class);
    }

    /**
     * Update a conversatioon
     *
     * @param  Conversation  $conversation
     *
     * @return Conversation
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function updateConversation(Conversation $conversation): Conversation
    {
        $updateConversationQuery = <<<'GQL'
            mutation updateConversationQuery($id: ID!, $set: ConversationPatch!) {
                updateConversation(input: {filter: {id: [$id]}, set: $set}) {
                    conversation {
                        id
                        od_id
                        name
                        description
                        interpreter
                        behaviors
                        conditions {
                            id
                        }
                        created_at
                        updated_at
                        scenario {
                          id
                        }
                        scenes {
                          id
                        }
                    }
                }
            }
        GQL;
        $conversation->setUpdatedAt(Carbon::now());

        $missing = array_diff([Conversation::UID], $conversation->hydratedFields());
        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the conversation supplied to ConversationDataClient::updateConversation(), but are required!", implode(",", $missing)));
        }


        $serializer = new Serializer(self::getNormalizers(), []);
        // Remove UID from patch fields. We can't change the UID
        $patchFields = array_diff($conversation->hydratedFields(), [Scenario::UID]);
        $tree = array_merge(Conversation::localFields(),
            [Conversation::BEHAVIORS => Behavior::FIELDS, Conversation::CONDITIONS => Condition::FIELDS]);

        $serializationTree = SerializationTreeHelper::filterSerializationTree($tree, $patchFields);
        $conversationData = $serializer->normalize($conversation, 'json', [AbstractNormalizer::ATTRIBUTES => $serializationTree]);
        $response = $this->client->query($updateConversationQuery, ['id' => $conversation->getUid(), 'set' => $conversationData]);
        return $serializer->denormalize($response['data']['updateConversation']['conversation'][0], Conversation::class);

    }

    /**
     * Delete a coonversation by uid
     *
     * @param  string  $conversationUid
     *
     * @return bool
     */
    public function deleteConversationByUid(string $conversationUid): bool
    {
        $deleteConversationQuery = <<<'GQL'
            mutation deleteConversation($id: ID!) {
                deleteConversation(filter: {id: [$id]}) {
                    conversation {
                        id
                    }
                }
            }
        GQL;

        $response = $this->client->query($deleteConversationQuery, ['id' => $conversationUid]);
        // Is this neccesary? We could just not care.
        if(empty($response['data']['deleteConversation']['conversation'])) {
            throw new ConversationObjectNotFoundException(sprintf('Conversation with uid %s could not be found',
                $conversationUid));
        }
        return true;
    }

    /**
     * Get a conversation with additional data for parent scenario and child scenes.
     *
     * @param  string  $conversationUid
     *
     * @return Conversation
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getScenarioWithFocusedConversation(string $conversationUid): Conversation {
        $getFocusedConversationQuery = <<<'GQL'
            query getFocusedConversationQuery($id : ID!) {
                getConversation(id: $id) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scenario {
                        id
                        od_id
                        name
                        description
                    }
                    scenes {
                        id
                        od_id
                        name
                        description
                    }
                }
            }
        GQL;

        $response = $this->client->query($getFocusedConversationQuery, ['id' => $conversationUid]);
        if($response['data']['getConversation'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Conversation with uid %s could not be found',
                $conversationUid));
        }
        $serializer = new Serializer(self::getNormalizers(), []);

        return $serializer->denormalize($response['data']['getConversation'], Conversation::class);
    }

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
        $getAllStartingConversations = <<<'GQL'
            query getAllStartingConversations($scenarioUids: [ID!]!) {
                queryConversation(filter: { behaviors: {eq: "STARTING" }}) @cascade(fields: ["scenario"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scenario(filter: {id: $scenarioUids}) {
                        id
                    }
                    scenes {
                        id
                    }
                }
            }
        GQL;

        $scenarioUids = $scenarios->map(fn($scenario) => $scenario->getUid());

        if($scenarioUids->contains(null)) {
            throw new InsufficientHydrationException("All scenarios passed to ConversationDataClient::getAllStartingConversations must have a UID!");
        }

        $response = $this->client->query($getAllStartingConversations, ['scenarioUids' => $scenarioUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryConversation'], ConversationCollection::class);
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
        $getAllOpenConversations = <<<'GQL'
            query getAllOpenConversations($scenarioUids: [ID!]!) {
                queryConversation(filter: { behaviors: {eq: "OPEN" }}) @cascade(fields: ["scenario"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scenario(filter: {id: $scenarioUids}) {
                        id
                    }
                    scenes {
                        id
                    }
                }
            }
        GQL;

        $scenarioUids = $scenarios->map(fn($scenario) => $scenario->getUid());

        if($scenarioUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All scenarios passed to %s must have a UID!", __METHOD__ ));
        }

        $response = $this->client->query($getAllOpenConversations, ['scenarioUids' => $scenarioUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryConversation'], ConversationCollection::class);
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
        $getAllConversationsInScenarios = <<<'GQL'
            query getAllConversationsInScenarios($scenarioUids: [ID!]!) {
                queryConversation @cascade(fields: ["scenario"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scenario(filter: {id: $scenarioUids}) {
                        id
                    }
                    scenes {
                        id
                    }
                }
            }
        GQL;

        $scenarioUids = $scenarios->map(fn($scenario) => $scenario->getUid());

        if($scenarioUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All scenarios passed to %s must have a UID!", __METHOD__ ));
        }

        $response = $this->client->query($getAllConversationsInScenarios, ['scenarioUids' => $scenarioUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryConversation'], ConversationCollection::class);
    }


    /**
     * Adds a new Scene.
     * The supplied Scene must reference an existing Conversation (i.e one with a UID)
     *
     * @param  Conversation  $conversation
     *
     * @return Conversation
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function addScene(Scene $scene): Scene {
        $addSceneMutation = <<<'GQL'
            mutation addScene($scene: AddSceneInput!) {
              addScene(input: [$scene]) {
                scene {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    conversation {
                        id
                    }
                    turns {
                      id
                    }
                }
              }
            }
        GQL;

        $scene->setCreatedAt(Carbon::now());
        $scene->setUpdatedAt(Carbon::now());

        // Required fields on Scene
        $missing = array_filter([
            Scene::OD_ID,
            Scene::NAME,
            Scene::CREATED_AT,
            Scene::UPDATED_AT,
            Scene::CONVERSATION
        ], fn
        ($required) =>
        !in_array($required, $scene->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields %s are missing from the scene supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }

        // Required fields on Conversation
        $missing = array_filter([Conversation::UID], fn($required) => !in_array($required, $scene->getConversation()
            ->hydratedFields()));
        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields %s are missing from the conversation attached to the scene supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        //TODO: Update to allow entering a full scene graph
        $sceneData = $serializer->normalize($scene, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                Scene::OD_ID,
                Scene::NAME,
                Scene::DESCRIPTION,
                Scene::INTERPRETER,
                Scene::CREATED_AT,
                Scene::UPDATED_AT,
                Scene::BEHAVIORS => Behavior::FIELDS,
                Scene::CONDITIONS => Condition::FIELDS,
                Scene::CONVERSATION => [Conversation::UID]
            ]
        ]);

        $response = $this->client->query($addSceneMutation, ['scene' => $sceneData]);
        return $serializer->denormalize($response['data']['addScene']['scene'][0], Scene::class);
    }

    /***
     * Retrive all conversations that belong to the given scenario
     */
    public function getAllScenesByConversation(Conversation $conversation, bool $shallow): SceneCollection {
        $getAllScenesByConversationQuery = <<<'GQL'
            query getAllScenesByConversation($conversationUid: ID!) {
                queryScene @cascade(fields: ["conversation"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    conversation(filter: {id: [$conversationUid]}) {
                        id
                    }
                    turns {
                        id
                    }
                }
            }
        GQL;

        $missing = array_filter([Conversation::UID], fn($required) =>
        !in_array($required, $conversation->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the conversation supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }

        $response = $this->client->query($getAllScenesByConversationQuery, ['conversationUid' => $conversation->getUid()]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryScene'], SceneCollection::class);
    }

    /**
     * Get Conversation with the provided uid
     *
     * @param  string  $sceneUid
     * @param  bool    $shallow
     *
     * @return Scene
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getSceneByUid(string $sceneUid, bool $shallow): Scene {
        $getSceneQuery = <<<'GQL'
            query getScene($id : ID!) {
                getScene(id: $id) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    conversation {
                        id
                    }
                    turns {
                        id
                    }
                }
            }
        GQL;

        $response = $this->client->query($getSceneQuery, ['id' => $sceneUid]);
        if($response['data']['getScene'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Scene with uid %s could not be found',
                $sceneUid));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        return  $serializer->denormalize($response['data']['getScene'], Scene::class);
    }

    /**
     * Update a conversatioon
     *
     * @param  Scene  $scene
     *
     * @return Scene
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function updateScene(Scene $scene): Scene
    {
        $updateSceneMutation = <<<'GQL'
            mutation updateScene($id: ID!, $set: ScenePatch!) {
                updateScene(input: {filter: {id: [$id]}, set: $set}) {
                    scene {
                        id
                        od_id
                        name
                        description
                        interpreter
                        behaviors
                        conditions {
                            id
                        }
                        created_at
                        updated_at
                        conversation {
                          id
                        }
                        turns {
                          id
                        }
                    }
                }
            }
        GQL;
        $scene->setUpdatedAt(Carbon::now());

        $missing = array_diff([Scene::UID], $scene->hydratedFields());
        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the scene supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }


        $serializer = new Serializer(self::getNormalizers(), []);
        // Remove UID from patch fields. We can't change the UID
        $patchFields = array_diff($scene->hydratedFields(), [Conversation::UID]);
        $tree = array_merge(Scene::localFields(),
            [Scene::BEHAVIORS => Behavior::FIELDS, Scene::CONDITIONS => Condition::FIELDS]);

        $serializationTree = SerializationTreeHelper::filterSerializationTree($tree, $patchFields);
        $data = $serializer->normalize($scene, 'json', [AbstractNormalizer::ATTRIBUTES => $serializationTree]);
        $response = $this->client->query($updateSceneMutation, ['id' => $scene->getUid(), 'set' => $data]);
        return $serializer->denormalize($response['data']['updateScene']['scene'][0], Scene::class);

    }

    /**
     * Delete a coonversation by uid
     *
     * @param  string  $sceneUid
     *
     * @return bool
     */
    public function deleteSceneByUid(string $sceneUid): bool
    {
        $deleteSceneQuery = <<<'GQL'
            mutation deleteScene($id: ID!) {
                deleteScene(filter: {id: [$id]}) {
                    scene {
                        id
                    }
                }
            }
        GQL;

        $response = $this->client->query($deleteSceneQuery, ['id' => $sceneUid]);
        // Is this neccesary? We could just not care.
        if(empty($response['data']['deleteScene']['scene'])) {
            throw new ConversationObjectNotFoundException(sprintf('Scene with uid %s could not be found',
                $sceneUid));
        }
        return true;
    }

    /**
     * Get a scenario with data focused around a scene suitable for the conversation builder.
     *
     * @param  string  $sceneUid
     *
     * @return Scene
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getScenarioWithFocusedScene(string $sceneUid): Scene {
        $getFocusedSceneQuery = <<<'GQL'
            query getFocusedScene($id : ID!) {
                getScene(id: $id) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    conversation {
                        id
                        od_id
                        name
                        description
                        scenario {
                          id
                          od_id
                          name
                          description
                        }
                    }
                    turns {
                        id
                        od_id
                        name
                        description
                    }
                }
            }
        GQL;

        $response = $this->client->query($getFocusedSceneQuery, ['id' => $sceneUid]);
        if($response['data']['getScene'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Scene with uid %s could not be found',
                $sceneUid));
        }
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['getScene'], Scene::class);
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
        $getAllStartingScenes = <<<'GQL'
            query getAllStartingScenes($conversationUids: [ID!]!) {
                queryScene(filter: { behaviors: {eq: "STARTING" }}) @cascade(fields: ["conversation"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    conversation(filter: {id: $conversationUids}) {
                        id
                    }
                    turns {
                        id
                    }
                }
            }
        GQL;

        $conversationUids = $conversations->map(fn($scenario) => $scenario->getUid());

        if($conversationUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All conversations passed to %s must have a UID!", __METHOD__));
        }

        $response = $this->client->query($getAllStartingScenes, ['conversationUids' => $conversationUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryScene'], SceneCollection::class);
    }


    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "starting" from the graph
     *
     * @param  ConversationCollection  $conversations
     * @param  bool                    $shallow
     *
     * @return SceneCollection
     */
    public function getAllOpenScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        $getAllOpenScenes = <<<'GQL'
            query getAllOpenScenes($conversationUids: [ID!]!) {
                queryScene(filter: { behaviors: {eq: "OPEN" }}) @cascade(fields: ["conversation"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    conversation(filter: {id: $conversationUids}) {
                        id
                    }
                    turns {
                        id
                    }
                }
            }
        GQL;

        $conversationUids = $conversations->map(fn($scenario) => $scenario->getUid());

        if($conversationUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All conversations passed to %s must have a UID!", __METHOD__));
        }

        $response = $this->client->query($getAllOpenScenes, ['conversationUids' => $conversationUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryScene'], SceneCollection::class);
    }

    /**
     * Retrieve all scenes that belong to the given conversations that have a behavior as "starting" from the graph
     *
     * @param  ConversationCollection  $conversations
     * @param  bool                    $shallow
     *
     * @return SceneCollection
     */
    public function getAllScenes(ConversationCollection $conversations, bool $shallow): SceneCollection
    {
        $getAllScenes = <<<'GQL'
            query getAllScenes($conversationUids: [ID!]!) {
                queryScene @cascade(fields: ["conversation"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    conversation(filter: {id: $conversationUids}) {
                        id
                    }
                    turns {
                        id
                    }
                }
            }
        GQL;

        $conversationUids = $conversations->map(fn($scenario) => $scenario->getUid());

        if($conversationUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All conversations passed to %s must have a UID!", __METHOD__));
        }

        $response = $this->client->query($getAllScenes, ['conversationUids' => $conversationUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryScene'], SceneCollection::class);
    }

    /**
     * Adds a new Turn.
     * The supplied Turn must reference an existing Scene (i.e one with a UID)
     *
     * @param  Turn  $turn
     *
     * @return Turn
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function addTurn(Turn $turn): Turn {
        $addTurnMutation = <<<'GQL'
            mutation addTurn($turn: AddTurnInput!) {
              addTurn(input: [$turn]) {
                turn {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    valid_origins
                    scene {
                        id
                    }

                    request_intents {
                        id
                    }

                    response_intents {
                        id
                    }
                }
              }
            }
        GQL;

        $turn->setCreatedAt(Carbon::now());
        $turn->setUpdatedAt(Carbon::now());

        // Required fields on Turn
        $missing = array_filter([
            Turn::OD_ID,
            Turn::NAME,
            Turn::CREATED_AT,
            Turn::UPDATED_AT,
            Turn::SCENE
        ], fn
        ($required) =>
        !in_array($required, $turn->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields %s are missing from the turn supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }

        // Required fields on Scene
        $missing = array_filter([Scene::UID], fn($required) => !in_array($required, $turn->getConversation()
            ->hydratedFields()));
        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields %s are missing from the scene attached to the turn supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        //TODO: Update to allow entering a full scene graph
        $data = $serializer->normalize($turn, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                Turn::OD_ID,
                Turn::NAME,
                Turn::DESCRIPTION,
                Turn::INTERPRETER,
                Turn::CREATED_AT,
                Turn::UPDATED_AT,
                Turn::BEHAVIORS => Behavior::FIELDS,
                Turn::CONDITIONS => Condition::FIELDS,
                Turn::VALID_ORIGINS => [Turn::UID],
                Turn::SCENE => [Scene::UID]
            ]
        ]);

        $response = $this->client->query($addTurnMutation, ['turn' => $data]);
        return $serializer->denormalize($response['data']['addTurn']['turn'][0], Turn::class);
    }


    /**
     * Get all Turns in a scene
     *
     * @param  Scene  $scene
     * @param  bool   $shallow
     *
     * @return TurnCollection
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getAllTurnsByScene(Scene $scene, bool $shallow): TurnCollection {
        $getAllTurnsBySceneQuery = <<<'GQL'
            query getAllTurnsByScene($sceneUid: ID!) {
                queryTurn @cascade(fields: ["scene"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scene(filter: {id: [$sceneUid]}) {
                        id
                    }
                    valid_origins
                    request_intents {
                        id
                    }
                    response_intents {
                        id
                    }
                }
            }
        GQL;

        $missing = array_filter([Scene::UID], fn($required) =>
        !in_array($required, $scene->hydratedFields()));

        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the scene supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }

        $response = $this->client->query($getAllTurnsBySceneQuery, ['sceneUid' => $scene->getUid()]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryTurn'], TurnCollection::class);
    }

    /**
     * Get Turn with the provided uid
     *
     * @param  string  $turnUid
     * @param  bool    $shallow
     *
     * @return Scene
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getTurnByUid(string $turnUid, bool $shallow): Turn {
        $getTurnQuery = <<<'GQL'
            query getTurn($id : ID!) {
                getTurn(id: $id) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scene {
                        id
                    }
                    valid_origins
                    request_intents {
                        id
                    }

                    response_intents {
                        id
                    }
                }
            }
        GQL;

        $response = $this->client->query($getTurnQuery, ['id' => $turnUid]);
        if($response['data']['getTurn'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Turn with uid %s could not be found',
                $turnUid));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        return  $serializer->denormalize($response['data']['getTurn'], Turn::class);
    }

    /**
     * Update a Turn
     *
     * @param  Scene  $turn
     *
     * @return Scene
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function updateTurn(Turn $turn): Turn
    {
        $updateTurnMutation = <<<'GQL'
            mutation updateTurn($id: ID!, $set: TurnPatch!) {
                updateTurn(input: {filter: {id: [$id]}, set: $set}) {
                    turn {
                        id
                        od_id
                        name
                        description
                        interpreter
                        behaviors
                        conditions {
                            id
                        }
                        created_at
                        updated_at
                        scene {
                            id
                        }
                        valid_origins
                        request_intents {
                            id
                        }

                        response_intents {
                            id
                        }
                    }
                }
            }
        GQL;
        $turn->setUpdatedAt(Carbon::now());

        $missing = array_diff([Turn::UID], $turn->hydratedFields());
        if(!empty($missing)) {
            throw new InsufficientHydrationException(sprintf("The fields '%s' are missing from the turn supplied to %s, but are required!", implode(",", $missing), __METHOD__));
        }

        $serializer = new Serializer(self::getNormalizers(), []);
        // Remove UID from patch fields. We can't change the UID
        $patchFields = array_diff($turn->hydratedFields(), [Turn::UID]);
        $tree = array_merge(Turn::localFields(),
            [Turn::BEHAVIORS => Behavior::FIELDS, Turn::CONDITIONS => Condition::FIELDS]);

        $serializationTree = SerializationTreeHelper::filterSerializationTree($tree, $patchFields);
        $data = $serializer->normalize($turn, 'json', [AbstractNormalizer::ATTRIBUTES => $serializationTree]);
        $response = $this->client->query($updateTurnMutation, ['id' => $turn->getUid(), 'set' => $data]);
        return $serializer->denormalize($response['data']['updateTurn']['turn'][0], Turn::class);

    }

    /**
     * Delete a Turn by uid
     *
     * @param  string  $turnUid
     *
     * @return bool
     */
    public function deleteTurnByUid(string $turnUid): bool
    {
        $deleteTurnQuery = <<<'GQL'
            mutation deleteTurn($id: ID!) {
                deleteTurn(filter: {id: [$id]}) {
                    turn {
                        id
                    }
                }
            }
        GQL;

        $response = $this->client->query($deleteTurnQuery, ['id' => $turnUid]);
        // Is this neccesary? We could just not care.
        if(empty($response['data']['deleteTurn']['turn'])) {
            throw new ConversationObjectNotFoundException(sprintf('Turn with uid %s could not be found',
                $turnUid));
        }
        return true;
    }

    /**
     * Get a scenario with data focused around a turn suitable for the conversation builder.
     *
     * @param  string  $turnUid
     *
     * @return Scene
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getScenarioWithFocusedTurn(string $turnUid): Turn {
        $getFocusedTurnQuery = <<<'GQL'
            query getFocusedTurn($id : ID!) {
                getTurn(id: $id) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    valid_origins
                    request_intents {
                        id
                        od_id
                        name
                        description
                    }

                    response_intents {
                        id
                        od_id
                        name
                        description
                    }
                }
            }
        GQL;

        $response = $this->client->query($getFocusedTurnQuery, ['id' => $turnUid]);
        if($response['data']['getTurn'] === null) {
            throw new ConversationObjectNotFoundException(sprintf('Turn with uid %s could not be found',
                $turnUid));
        }
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['getTurn'], Turn::class);
    }


    /**
     * Retrieve all turn that belong to the given scenes that have the "STARTING" behavior.
     *
     * @param  SceneCollection  $scenes
     * @param  bool             $shallow
     *
     * @return TurnCollection
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getAllStartingTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        $getAllStartingTurns = <<<'GQL'
            query getAllStartingTurns($sceneUids: [ID!]!) {
                queryTurn(filter: { behaviors: {eq: "STARTING" }}) @cascade(fields: ["scene"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scene(filter: {id: $sceneUids}) {
                        id
                    }
                    valid_origins
                    request_intents {
                        id
                    }
                    response_intents {
                        id
                    }
                }
            }
        GQL;

        $sceneUids = $scenes->map(fn($scenario) => $scenario->getUid());

        if($sceneUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All scenes passed to %s must have a UID!", __METHOD__));
        }

        $response = $this->client->query($getAllStartingTurns, ['sceneUids' => $sceneUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryTurn'], TurnCollection::class);
    }


    /**
     * Retrieve all turn that belong to the given scenes that have the "OPEN" behavior.
     *
     * @param  SceneCollection  $scenes
     * @param  bool             $shallow
     *
     * @return TurnCollection
     */
    public function getAllOpenTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        $getAllOpenTurnsQuery = <<<'GQL'
            query getAllOpenTurns($sceneUids: [ID!]!) {
                queryTurn(filter: { behaviors: {eq: "OPEN" }}) @cascade(fields: ["scene"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scene(filter: {id: $sceneUids}) {
                        id
                    }
                    valid_origins
                    request_intents {
                        id
                    }
                    response_intents {
                        id
                    }
                }
            }
        GQL;

        $sceneUids = $scenes->map(fn($scenario) => $scenario->getUid());

        if($sceneUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All scenes passed to %s must have a UID!", __METHOD__));
        }

        $response = $this->client->query($getAllOpenTurnsQuery, ['sceneUids' => $sceneUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryTurn'], TurnCollection::class);
    }

    /**
     * Retrieve all turns that belong to the given scenes.
     *
     * @param  SceneCollection  $scenes
     * @param  bool             $shallow
     *
     * @return TurnCollection
     */
    public function getAllTurns(SceneCollection $scenes, bool $shallow): TurnCollection
    {
        $getAllTurnsQuery = <<<'GQL'
            query getAllTurns($sceneUids: [ID!]!) {
                queryTurn @cascade(fields: ["scene"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scene(filter: {id: $sceneUids}) {
                        id
                    }
                    valid_origins
                    request_intents {
                        id
                    }
                    response_intents {
                        id
                    }
                }
            }
        GQL;

        $sceneUids = $scenes->map(fn($scenario) => $scenario->getUid());

        if($sceneUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All scenes passed to %s must have a UID!", __METHOD__));
        }

        $response = $this->client->query($getAllTurnsQuery, ['sceneUids' => $sceneUids]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryTurn'], TurnCollection::class);
    }

    /**
     * Retrieve all turns that belong to given scenes and that have the specified validOrigin
     * Valid origin is an od_id
     *
     * @param  SceneCollection  $scenes
     * @param  string           $validOrigin
     * @param  bool             $shallow
     *
     * @return TurnCollection
     */
    public function getAllTurnsByValidOrigin(SceneCollection $scenes, string $validOrigin, bool $shallow): TurnCollection {
        $getAllTurnsByValidOriginQuery = <<<'GQL'
            query getAllTurnsByValidOrigin($sceneUids: [ID!]!, $originOdId: String!) {
                queryTurn(filter:  {valid_origins: {eq: $originOdId}}) @cascade(fields: ["scene", "valid_origins"]) {
                    id
                    od_id
                    name
                    description
                    interpreter
                    behaviors
                    conditions {
                        id
                    }
                    created_at
                    updated_at
                    scene(filter: {id: $sceneUids}) {
                        id
                    }
                    valid_origins
                    request_intents {
                        id
                    }
                    response_intents {
                        id
                    }
                }
            }
        GQL;

        $sceneUids = $scenes->map(fn($scenario) => $scenario->getUid());

        if($sceneUids->contains(null)) {
            throw new InsufficientHydrationException(sprintf("All scenes passed to %s must have a UID!", __METHOD__));
        }

        $response = $this->client->query($getAllTurnsByValidOriginQuery, ['sceneUids' => $sceneUids, 'originOdId' => $validOrigin]);
        $serializer = new Serializer(self::getNormalizers(), []);
        return $serializer->denormalize($response['data']['queryTurn'], TurnCollection::class);
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
