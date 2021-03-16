<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;


use DateTime;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\DataClients\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

class ConversationDataClientQueriesTest extends TestCase
{
    protected ConversationDataClient $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->resetGraphQL();
        $this->client = resolve(ConversationDataClient::class);
    }

    public function resetGraphQL()
    {
        $client = resolve(GraphQLClientInterface::class);
        $client->dropAll();
        $client->setSchema(config('opendialog.graphql.schema'));
    }

    public function getStandaloneScenario()
    {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario");
        $scenario->setName("Test Scenario");
        $scenario->setDescription("A test scenario");
        $scenario->setInterpreter("interpreter.core.example");
        $scenario->setStatus(Scenario::DRAFT_STATUS);
        $scenario->setActive(true);
        $scenario->setBehaviors(new BehaviorsCollection([new Behavior("STARTING")]));
        $scenario->setConditions(new ConditionCollection());
        $scenario->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $scenario->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $scenario->setConversations(new ConversationCollection());
        return $scenario;
    }

    public function getStandaloneConversation()
    {
        $conversation = new Conversation();
        $conversation->setOdId("test_conversation");
        $conversation->setName("Test Conversation");
        $conversation->setDescription("A test conversation");
        $conversation->setInterpreter("interpreter.core.example");
        $conversation->setBehaviors(new BehaviorsCollection([new Behavior("STARTING")]));
        $conversation->setConditions(new ConditionCollection());
        $conversation->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $conversation->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $conversation->setScenes(new SceneCollection());
        return $conversation;
    }

    public function getStandaloneScene()
    {
        $scene = new Scene();
        $scene->setOdId("test_scene");
        $scene->setName("Test Scene");
        $scene->setDescription("A test scene");
        $scene->setInterpreter("interpreter.core.example");
        $scene->setBehaviors(new BehaviorsCollection([new Behavior("STARTING")]));
        $scene->setConditions(new ConditionCollection());
        $scene->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $scene->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $scene->setTurns(new TurnCollection());
        return $scene;
    }

    public function getStandaloneTurn()
    {
        $turn = new Turn();
        $turn->setOdId("test_turn");
        $turn->setName("Test Turn");
        $turn->setDescription("A test turn");
        $turn->setInterpreter("interpreter.core.example");
        $turn->setBehaviors(new BehaviorsCollection([new Behavior("STARTING")]));
        $turn->setConditions(new ConditionCollection());
        $turn->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $turn->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $turn->setValidOrigins(['origin_a', 'origin_b']);
        return $turn;
    }









}
