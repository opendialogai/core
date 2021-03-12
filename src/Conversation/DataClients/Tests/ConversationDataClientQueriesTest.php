<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;


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

    public function testGetAllActiveScenarios() {
        $activeDraftScenario = new Scenario();
        $activeDraftScenario->setOdId("active_draft_scenario");
        $activeDraftScenario->setName("Active (Draft) Scenario");
        $activeDraftScenario->setStatus("DRAFT");
        $activeDraftScenario->setActive(true);


        $activeLiveScenario = new Scenario();
        $activeLiveScenario->setOdId("active_Live_scenario");
        $activeLiveScenario->setName("Active (Live) Scenario");
        $activeLiveScenario->setStatus("LIVE");
        $activeLiveScenario->setActive(true);

        $inactiveScenario = new Scenario();
        $inactiveScenario->setOdId("inactive_scenario");
        $inactiveScenario->setName("inactive Scenario");
        $inactiveScenario->setStatus("DRAFT");
        $inactiveScenario->setActive(false);

        $this->client->addScenario($activeDraftScenario);
        $this->client->addScenario($activeLiveScenario);
        $this->client->addScenario($inactiveScenario);

        $scenarios = $this->client->getAllActiveScenarios(false);
        $this->assertEquals(1,$scenarios->count());
        $this->assertEquals($activeLiveScenario->getOdId(), $scenarios[0]->getOdId());
        $this->assertEquals("LIVE", $scenarios[0]->getStatus());
        $this->assertEquals(true, $scenarios[0]->isActive());
        $this->assertEquals(new ConversationCollection(), $scenarios[0]->getConversations());

    }

    public function testGetAllScenarios()
    {
        $testScenario = $this->getStandaloneScenario();
        $this->client->addScenario($testScenario);

        $scenarios = $this->client->getAllScenarios(false);
        $this->assertEquals(1, $scenarios->count());
        $scenario = $scenarios[0];
        $this->assertNotNull($scenario->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenario->getOdId());
        $this->assertEquals($testScenario->getName(), $scenario->getName());
        $this->assertEquals($testScenario->isActive(), $scenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $scenario->getStatus());
        $this->assertEquals(new ConversationCollection(), $scenario->getConversations());


    }



    public function testGetScenario()
    {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());

        $scenario = $this->client->getScenarioByUid($testScenario->getUid(), false);
        $this->assertNotNull($scenario->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenario->getOdId());
        $this->assertEquals($testScenario->getName(), $scenario->getName());
        $this->assertEquals($testScenario->isActive(), $scenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $scenario->getStatus());
        $this->assertEquals(new ConversationCollection(), $scenario->getConversations());

    }

    public function testGetScenarioNonExistantUid() {
        $this->expectException(ConversationObjectNotFoundException::class);
        $this->client->getScenarioByUid("0x0001", false);
    }

    public function testAddScenario()
    {
        $testScenario = $this->getStandaloneScenario();
        $scenario = $this->client->addScenario($testScenario);

        $this->assertIsString($scenario->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenario->getOdId());
        $this->assertEquals($testScenario->getName(), $scenario->getName());
        $this->assertEquals($testScenario->getDescription(), $scenario->getDescription());
        $this->assertEquals($testScenario->getBehaviors(), $scenario->getBehaviors());
        $this->assertEquals($testScenario->getConditions(), $scenario->getConditions());
        $this->assertEquals($testScenario->getInterpreter(), $scenario->getInterpreter());
        $this->assertEquals($testScenario->isActive(), $scenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $scenario->getStatus());
        $this->assertEquals(new ConversationCollection(), $scenario->getConversations());
    }

    public function testAddScenarioMissingFields() {
        $scenario = new Scenario();
        $scenario->setOdId("test_scenario");
        $scenario->setName("Test Scenario");
        // Active and status are also required.
        $this->expectException(InsufficientHydrationException::class);
        $addedScenario = $this->client->addScenario($scenario);
    }

    public function testDeleteScenario()
    {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $success = $this->client->deleteScenarioByUid($scenario->getUid());
        //Todo: Check for deletion cascade.

        $this->assertEquals(true, $success);
    }

    public function testDeleteScenarioNonExistantUid() {
        $this->expectException(ConversationObjectNotFoundException::class);
        $this->client->deleteScenarioByUid("0x0001");
    }

    public function testUpdateScenario()
    {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());

        $changes = new Scenario();
        $changes->setUid($testScenario->getUid());
        $changes->setName("Updated name");
        $changes->setOdId("updated_id");
        $changes->setActive(false);

        $updatedScenario = $this->client->updateScenario($changes);
        $this->assertEquals($testScenario->getUid(), $updatedScenario->getUid());
        $this->assertEquals($changes->getOdId(), $updatedScenario->getOdId());
        $this->assertEquals($changes->getName(), $updatedScenario->getName());
        $this->assertEquals($testScenario->getDescription(), $updatedScenario->getDescription());
        $this->assertEquals($testScenario->getBehaviors(), $updatedScenario->getBehaviors());
        $this->assertEquals($testScenario->getConditions(), $updatedScenario->getConditions());
        $this->assertEquals($testScenario->getInterpreter(), $updatedScenario->getInterpreter());
        $this->assertEquals($testScenario->getCreatedAt(), $updatedScenario->getCreatedAt());
        $this->assertEquals($testScenario->getUpdatedAt(), $updatedScenario->getUpdatedAt());
        $this->assertEquals($changes->isActive(), $updatedScenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $updatedScenario->getStatus());
        $this->assertEquals($testScenario->getConversations(), $updatedScenario->getConversations());
    }

    public function testUpdateScenarioNoChanges() {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());

        $changes = new Scenario();
        $changes->setUid($testScenario->getUid());
        $updatedScenario = $this->client->updateScenario($changes);
        $this->assertEquals($testScenario->getUid(), $updatedScenario->getUid());
        $this->assertEquals($testScenario->getOdId(), $updatedScenario->getOdId());
        $this->assertEquals($testScenario->getName(), $updatedScenario->getName());
        $this->assertEquals($testScenario->getDescription(), $updatedScenario->getDescription());
        $this->assertEquals($testScenario->getBehaviors(), $updatedScenario->getBehaviors());
        $this->assertEquals($testScenario->getConditions(), $updatedScenario->getConditions());
        $this->assertEquals($testScenario->getInterpreter(), $updatedScenario->getInterpreter());
        $this->assertEquals($testScenario->getCreatedAt(), $updatedScenario->getCreatedAt());
        $this->assertEquals($testScenario->isActive(), $updatedScenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $updatedScenario->getStatus());
        $this->assertEquals($testScenario->getConversations(), $updatedScenario->getConversations());
    }

    public function testUpdateScenarioNoUID() {
        $changes = new Scenario();
        $changes->setName("New name");
        $this->expectException(InsufficientHydrationException::class);
        $this->client->updateScenario($changes);
    }

    public function testAddConversation() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());

        $testConversation = new Conversation();
        $testConversation->setOdId("test_conversation");
        $testConversation->setName("Test Conversation");
        $testConversation->setScenario($scenario);
        $conversation = $this->client->addConversation($testConversation);

        $this->assertIsString($conversation->getUid());
        $this->assertEquals($testConversation->getOdId(), $conversation->getOdId());
        $this->assertEquals($testConversation->getName(), $conversation->getName());
        $this->assertEquals($testConversation->getScenario()->getUid(), $conversation->getScenario()->getUid());
        $this->assertEquals(new ConditionCollection(), $conversation->getConditions());
        $this->assertEquals(new BehaviorsCollection(), $conversation->getBehaviors());
        $this->assertEquals($testConversation->getInterpreter(), $conversation->getInterpreter());
        $this->assertEquals(new SceneCollection(), $conversation->getScenes());

    }

    public function testGetConversationsByScenario() {

        $scenarioA = new Scenario();
        $scenarioA->setOdId("scenario_a");
        $scenarioA->setName("Scenario A");
        $scenarioA->setActive(true);
        $scenarioA->setStatus(Scenario::LIVE_STATUS);

        $scenarioB = new Scenario();
        $scenarioB->setOdId("scenario_b");
        $scenarioB->setName("Scenario B");
        $scenarioB->setActive(true);
        $scenarioB->setStatus(Scenario::LIVE_STATUS);

        $scenarioA = $this->client->addScenario($scenarioA);
        $scenarioB = $this->client->addScenario($scenarioB);

        $conversationA = new Conversation();
        $conversationA->setOdId("conversation_a");
        $conversationA->setName("Conversation A");
        $conversationA->setScenario($scenarioA);

        $conversationB = new Conversation();
        $conversationB->setOdId("conversation_b");
        $conversationB->setName("Conversation B");
        $conversationB->setScenario($scenarioB);

        $conversationA = $this->client->addConversation($conversationA);
        $conversationB = $this->client->addConversation($conversationB);

        $conversationsInScenarioA = $this->client->getAllConversationsByScenario($scenarioA, false);
        $this->assertEquals(1, $conversationsInScenarioA->count());
        $this->assertEquals($conversationA->getUid(), $conversationsInScenarioA[0]->getUid());

        $conversationsInScenarioB = $this->client->getAllConversationsByScenario($scenarioB, false);
        $this->assertEquals(1, $conversationsInScenarioB->count());
        $this->assertEquals($conversationB->getUid(), $conversationsInScenarioB[0]->getUid());

    }

    public function testGetConversationByUid() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());

        $testConversation = new Conversation();
        $testConversation->setOdId("test_conversation");
        $testConversation->setName("Test Conversation");
        $testConversation->setScenario($scenario);

        $testConversation = $this->client->addConversation($testConversation);

        $conversation = $this->client->getConversationByUid($testConversation->getUid(), false);
        $this->assertNotNull($conversation->getUid());
        $this->assertEquals($testConversation->getOdId(), $conversation->getOdId());
        $this->assertEquals($testConversation->getName(), $conversation->getName());
        $this->assertEquals(new SceneCollection(), $conversation->getScenes());


    }

    public function testGetConversationNonExistantUid() {
        $this->expectException(ConversationObjectNotFoundException::class);
        $this->client->getConversationByUid("0x0001", false);
    }

    public function testUpdateConversation() {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());
        $testConversation = new Conversation();
        $testConversation->setOdId("test_conversation");
        $testConversation->setName("Test conversation");
        $testConversation->setScenario($testScenario);
        $testConversation = $this->client->addConversation($testConversation);


        $changes = new Conversation();
        $changes->setUid($testConversation->getUid());
        $changes->setName("Updated name");
        $changes->setOdId("updated_id");

        $updatedConversation = $this->client->updateConversation($changes);
        $this->assertEquals($testConversation->getUid(), $updatedConversation->getUid());
        $this->assertEquals($changes->getOdId(), $updatedConversation->getOdId());
        $this->assertEquals($changes->getName(), $updatedConversation->getName());
        $this->assertEquals($testConversation->getDescription(), $updatedConversation->getDescription());
        $this->assertEquals($testConversation->getBehaviors(), $updatedConversation->getBehaviors());
        $this->assertEquals($testConversation->getConditions(), $updatedConversation->getConditions());
        $this->assertEquals($testConversation->getInterpreter(), $updatedConversation->getInterpreter());
        $this->assertEquals($testConversation->getCreatedAt(), $updatedConversation->getCreatedAt());
        $this->assertEquals(new SceneCollection(), $updatedConversation->getScenes());

    }

    public function testDeleteConversation() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversation = new Conversation();
        $conversation->setOdId("test_conversation");
        $conversation->setName("Test conversation");
        $conversation->setScenario($scenario);
        $conversation = $this->client->addConversation($conversation);

        $success = $this->client->deleteConversationByUid($conversation->getUid());
        //Todo: Check for deletion cascade.
        $this->assertEquals(true, $success);
    }

    public function testGetStartingConversationsInScenarios() {
        /**
         * Scenario A -> [Conversation A (STARTING)]
         * Scenario B -> [Conversation B (STARTING), Conversation D (COMPLETING,STARTING)]
         * Scenario C -> []
         */
        $scenarioA = new Scenario();
        $scenarioA->setOdId("scenario_a");
        $scenarioA->setName("Scenario A");
        $scenarioA->setStatus(Scenario::LIVE_STATUS);
        $scenarioA->setActive(true);
        $scenarioA = $this->client->addScenario($scenarioA);

        $scenarioB = new Scenario();
        $scenarioB->setOdId("scenario_b");
        $scenarioB->setName("Scenario B");
        $scenarioB->setStatus(Scenario::LIVE_STATUS);
        $scenarioB->setActive(true);
        $scenarioB = $this->client->addScenario($scenarioB);

        $scenarioC = new Scenario();
        $scenarioC->setOdId("scenario_c");
        $scenarioC->setName("Scenario C");
        $scenarioC->setStatus(Scenario::LIVE_STATUS);
        $scenarioC->setActive(true);
        $scenarioC = $this->client->addScenario($scenarioC);

        $conversationA = new Conversation();
        $conversationA->setOdId("conversation_a");
        $conversationA->setName("Conversation A");
        $conversationA->setScenario($scenarioA);
        $conversationA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
        $conversationA = $this->client->addConversation($conversationA);

        $conversationB = new Conversation();
        $conversationB->setOdId("conversation_b");
        $conversationB->setName("Conversation B");
        $conversationB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING)]));
        $conversationB->setScenario($scenarioB);
        $conversationB = $this->client->addConversation($conversationB);

        $conversationD = new Conversation();
        $conversationD->setOdId("conversation_d");
        $conversationD->setName("Conversation D");
        $conversationD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::STARTING)]));
        $conversationD->setScenario($scenarioB);
        $conversationD = $this->client->addConversation($conversationD);

        $startingConversations = $this->client->getAllStartingConversations(new ScenarioCollection([$scenarioA, $scenarioB,
            $scenarioC]), false);
        $this->assertEquals(2, $startingConversations->count());
        $this->assertEquals($conversationA->getUid(), $startingConversations[0]->getUid());
        $this->assertEquals($scenarioA->getUid(), $startingConversations[0]->getScenario()->getUid());
        $this->assertEquals($conversationD->getUid(), $startingConversations[1]->getUid());
        $this->assertEquals($scenarioB->getUid(), $startingConversations[1]->getScenario()->getUid());

    }

    public function testGetScenarioWithFocusedConversation() {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversation = new Conversation();
        $conversation->setOdId("test_conversation");
        $conversation->setName("Test Conversation");
        $conversation->setScenario($testScenario);
        $conversation = $this->client->addConversation($conversation);

        $sceneA = new Scene();
        $sceneA->setOdId("scene_a");
        $sceneA->setName("Scene A");
        $sceneA->setConversation($conversation);
        $sceneA = $this->client->addScene($sceneA);

        $sceneB = new Scene();
        $sceneB->setOdId("scene_b");
        $sceneB->setName("Scene B");
        $sceneB->setConversation($conversation);
        $sceneB = $this->client->addScene($sceneB);

        $scenarioTree = $this->client->getScenarioWithFocusedConversation($conversation->getUid());
        $this->assertEquals($testScenario->getUid(), $scenarioTree->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenarioTree->getOdId());
        $this->assertNotNull($scenarioTree->getConversations());
        $this->assertEquals(1, $scenarioTree->getConversations()->count());

        $conversationTree = $scenarioTree->getConversations()->first();
        $this->assertEquals($conversation->getUid(), $conversationTree->getUid());
        $this->assertEquals($conversation->getOdId(), $conversationTree->getOdId());
        $this->assertEquals($conversation->getName(), $conversationTree->getName());
        $this->assertNotNull($conversationTree->getScenes());
        $this->assertEquals(2, $conversationTree->getScenes()->count());

        $this->assertEquals($scenarioTree, $conversationTree->getScenario());

        $sceneATree = $conversationTree->getScenes()[0];
        $this->assertEquals($sceneA->getUid(), $sceneATree->getUid());
        $this->assertEquals($sceneA->getOdId(), $sceneATree->getOdId());
        $this->assertEquals($sceneA->getName(), $sceneATree->getName());
        $this->assertEquals($conversationTree, $sceneATree->getConversation());


        $sceneBTree = $conversationTree->getScenes()[1];
        $this->assertEquals($sceneB->getUid(), $sceneBTree->getUid());
        $this->assertEquals($sceneB->getOdId(), $sceneBTree->getOdId());
        $this->assertEquals($sceneB->getName(), $sceneBTree->getName());
        $this->assertEquals($conversationTree, $sceneBTree->getConversation());

    }

    public function testAddScene() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversation = $this->getStandaloneConversation();
        $conversation->setScenario($scenario);
        $conversation = $this->client->addConversation($conversation);

        $testScene = new Scene();
        $testScene->setOdId("test_scene");
        $testScene->setName("Test Scene");
        $testScene->setConversation($conversation);

        $scene = $this->client->addScene($testScene);

        $this->assertIsString($scene->getUid());
        $this->assertEquals($testScene->getOdId(), $scene->getOdId());
        $this->assertEquals($testScene->getName(), $scene->getName());
        $this->assertEquals($testScene->getConversation()->getUid(), $scene->getConversation()->getUid());
        $this->assertEquals(new ConditionCollection(), $scene->getConditions());
        $this->assertEquals(new BehaviorsCollection(), $scene->getBehaviors());
        $this->assertEquals($testScene->getInterpreter(), $scene->getInterpreter());
        $this->assertEquals(new TurnCollection(), $scene->getTurns());

    }





}
