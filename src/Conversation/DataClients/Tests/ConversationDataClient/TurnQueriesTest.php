<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;


use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;

class TurnQueriesTest extends ConversationDataClientQueriesTest
{
    public function testAddTurn() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversation = $this->getStandaloneConversation();
        $conversation->setScenario($scenario);
        $conversation = $this->client->addConversation($conversation);

        $scene = $this->getStandaloneScene();
        $scene->setConversation($conversation);
        $scene = $this->client->addScene($scene);

        $testTurn = new Turn();
        $testTurn->setOdId("test_turn");
        $testTurn->setName("Test Turn");
        $testTurn->setScene($scene);
        $turn = $this->client->addTurn($testTurn);

        $this->assertIsString($turn->getUid());
        $this->assertEquals($testTurn->getOdId(), $turn->getOdId());
        $this->assertEquals($testTurn->getName(), $turn->getName());
        $this->assertEquals($testTurn->getScene()->getUid(), $turn->getScene()->getUid());
        $this->assertEquals(new ConditionCollection(), $turn->getConditions());
        $this->assertEquals(new BehaviorsCollection(), $turn->getBehaviors());
        $this->assertEquals($testTurn->getInterpreter(), $turn->getInterpreter());
        $this->assertEquals(new IntentCollection(), $turn->getRequestIntents());
        $this->assertEquals(new IntentCollection(), $turn->getResponseIntents());
        $this->assertEquals(new TurnCollection(), $turn->getValidOrigins());

    }
//
//    public function testGetScenesByConversation() {
//        $conversationA = new Conversation();
//        $conversationA->setOdId("scenario_a");
//        $conversationA->setName("Scenario A");
//
//        $conversationB = new Conversation();
//        $conversationB->setOdId("scenario_b");
//        $conversationB->setName("Scenario B");
//
//        $conversationA = $this->client->addConversation($conversationA);
//        $conversationB = $this->client->addConversation($conversationB);
//
//        $sceneA = new Scene();
//        $sceneA->setOdId("conversation_a");
//        $sceneA->setName("Conversation A");
//        $sceneA->setConversation($conversationA);
//
//        $sceneB = new Scene();
//        $sceneB->setOdId("conversation_b");
//        $sceneB->setName("Conversation B");
//        $sceneB->setConversation($conversationB);
//
//        $sceneA = $this->client->addScene($sceneA);
//        $sceneB = $this->client->addScene($sceneB);
//
//        $scenesInConversationA = $this->client->getAllScenesByConversation($conversationA, false);
//        $this->assertEquals(1, $scenesInConversationA->count());
//        $this->assertEquals($sceneA->getUid(), $scenesInConversationA[0]->getUid());
//
//        $scenesInConversationB = $this->client->getAllScenesByConversation($conversationB, false);
//        $this->assertEquals(1, $scenesInConversationB->count());
//        $this->assertEquals($sceneB->getUid(), $scenesInConversationB[0]->getUid());
//
//    }
//
//    public function testGetSceneByUid() {
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//
//        $testScene = new Scene();
//        $testScene->setOdId("test_scene");
//        $testScene->setName("Test Scene");
//        $testScene->setConversation($conversation);
//        $testScene = $this->client->addScene($testScene);
//
//        $scene = $this->client->getSceneByUid($testScene->getUid(), false);
//        $this->assertNotNull($scene->getUid());
//        $this->assertEquals($testScene->getOdId(), $scene->getOdId());
//        $this->assertEquals($testScene->getName(), $scene->getName());
//        $this->assertEquals(new TurnCollection(), $scene->getTurns());
//
//
//    }
//
//    public function testGetSceneNonExistandUid() {
//        $this->expectException(ConversationObjectNotFoundException::class);
//        $this->client->getSceneByUid("0x0001", false);
//    }
//
//    public function testUpdateScene() {
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        $testScene = new Scene();
//        $testScene->setOdId("test_scene");
//        $testScene->setName("Test Scene");
//        $testScene->setConversation($conversation);
//        $testScene = $this->client->addScene($testScene);
//
//        $changes = new Scene();
//        $changes->setUid($testScene->getUid());
//        $changes->setName("Updated name");
//        $changes->setOdId("updated_id");
//
//        $updateScene = $this->client->updateScene($changes);
//        $this->assertEquals($testScene->getUid(), $updateScene->getUid());
//        $this->assertEquals($changes->getOdId(), $updateScene->getOdId());
//        $this->assertEquals($changes->getName(), $updateScene->getName());
//        $this->assertEquals($testScene->getDescription(), $updateScene->getDescription());
//        $this->assertEquals($testScene->getBehaviors(), $updateScene->getBehaviors());
//        $this->assertEquals($testScene->getConditions(), $updateScene->getConditions());
//        $this->assertEquals($testScene->getInterpreter(), $updateScene->getInterpreter());
//        $this->assertEquals($testScene->getCreatedAt(), $updateScene->getCreatedAt());
//        $this->assertEquals(new TurnCollection(), $updateScene->getTurns());
//
//    }
//
//    public function testDeleteScene() {
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        $testScene = new Scene();
//        $testScene->setOdId("test_scene");
//        $testScene->setName("Test Scene");
//        $testScene->setConversation($conversation);
//        $testScene = $this->client->addScene($testScene);
//
//        $success = $this->client->deleteSceneByUid($testScene->getUid());
//        //Todo: Check for deletion cascade.
//        $this->assertEquals(true, $success);
//    }
//
//
//
//    public function testGetStartingConversationsInScenarios() {
//        /**
//         * Scenario A -> [Conversation A (STARTING)]
//         * Scenario B -> [Conversation B (COMPLETING), Conversation D (COMPLETING,STARTING)]
//         * Scenario C -> []
//         */
//        $scenarioA = new Scenario();
//        $scenarioA->setOdId("scenario_a");
//        $scenarioA->setName("Scenario A");
//        $scenarioA->setStatus(Scenario::LIVE_STATUS);
//        $scenarioA->setActive(true);
//        $scenarioA = $this->client->addScenario($scenarioA);
//
//        $scenarioB = new Scenario();
//        $scenarioB->setOdId("scenario_b");
//        $scenarioB->setName("Scenario B");
//        $scenarioB->setStatus(Scenario::LIVE_STATUS);
//        $scenarioB->setActive(true);
//        $scenarioB = $this->client->addScenario($scenarioB);
//
//        $scenarioC = new Scenario();
//        $scenarioC->setOdId("scenario_c");
//        $scenarioC->setName("Scenario C");
//        $scenarioC->setStatus(Scenario::LIVE_STATUS);
//        $scenarioC->setActive(true);
//        $scenarioC = $this->client->addScenario($scenarioC);
//
//        $conversationA = new Conversation();
//        $conversationA->setOdId("conversation_a");
//        $conversationA->setName("Conversation A");
//        $conversationA->setScenario($scenarioA);
//        $conversationA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
//        $conversationA = $this->client->addConversation($conversationA);
//
//        $conversationB = new Conversation();
//        $conversationB->setOdId("conversation_b");
//        $conversationB->setName("Conversation B");
//        $conversationB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING)]));
//        $conversationB->setScenario($scenarioB);
//        $conversationB = $this->client->addConversation($conversationB);
//
//        $conversationD = new Conversation();
//        $conversationD->setOdId("conversation_d");
//        $conversationD->setName("Conversation D");
//        $conversationD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::STARTING)]));
//        $conversationD->setScenario($scenarioB);
//        $conversationD = $this->client->addConversation($conversationD);
//
//        $startingConversations = $this->client->getAllStartingConversations(new ScenarioCollection([$scenarioA, $scenarioB,
//            $scenarioC]), false);
//        $this->assertEquals(2, $startingConversations->count());
//        $this->assertEquals($conversationA->getUid(), $startingConversations[0]->getUid());
//        $this->assertEquals($scenarioA->getUid(), $startingConversations[0]->getScenario()->getUid());
//        $this->assertEquals($conversationD->getUid(), $startingConversations[1]->getUid());
//        $this->assertEquals($scenarioB->getUid(), $startingConversations[1]->getScenario()->getUid());
//
//    }
//
//    public function testGetOpenConversationsInScenarios() {
//        /**
//         * Scenario A -> [Conversation A (OPEN)]
//         * Scenario B -> [Conversation B (STARTING), Conversation D (COMPLETING,OPEN)]
//         * Scenario C -> []
//         */
//        $scenarioA = new Scenario();
//        $scenarioA->setOdId("scenario_a");
//        $scenarioA->setName("Scenario A");
//        $scenarioA->setStatus(Scenario::LIVE_STATUS);
//        $scenarioA->setActive(true);
//        $scenarioA = $this->client->addScenario($scenarioA);
//
//        $scenarioB = new Scenario();
//        $scenarioB->setOdId("scenario_b");
//        $scenarioB->setName("Scenario B");
//        $scenarioB->setStatus(Scenario::LIVE_STATUS);
//        $scenarioB->setActive(true);
//        $scenarioB = $this->client->addScenario($scenarioB);
//
//        $scenarioC = new Scenario();
//        $scenarioC->setOdId("scenario_c");
//        $scenarioC->setName("Scenario C");
//        $scenarioC->setStatus(Scenario::LIVE_STATUS);
//        $scenarioC->setActive(true);
//        $scenarioC = $this->client->addScenario($scenarioC);
//
//        $conversationA = new Conversation();
//        $conversationA->setOdId("conversation_a");
//        $conversationA->setName("Conversation A");
//        $conversationA->setScenario($scenarioA);
//        $conversationA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN)]));
//        $conversationA = $this->client->addConversation($conversationA);
//
//        $conversationB = new Conversation();
//        $conversationB->setOdId("conversation_b");
//        $conversationB->setName("Conversation B");
//        $conversationB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
//        $conversationB->setScenario($scenarioB);
//        $conversationB = $this->client->addConversation($conversationB);
//
//        $conversationD = new Conversation();
//        $conversationD->setOdId("conversation_d");
//        $conversationD->setName("Conversation D");
//        $conversationD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::OPEN)]));
//        $conversationD->setScenario($scenarioB);
//        $conversationD = $this->client->addConversation($conversationD);
//
//        $startingConversations = $this->client->getAllOpenConversations(new ScenarioCollection([$scenarioA, $scenarioB,
//            $scenarioC]), false);
//        $this->assertEquals(2, $startingConversations->count());
//        $this->assertEquals($conversationA->getUid(), $startingConversations[0]->getUid());
//        $this->assertEquals($scenarioA->getUid(), $startingConversations[0]->getScenario()->getUid());
//        $this->assertEquals($conversationD->getUid(), $startingConversations[1]->getUid());
//        $this->assertEquals($scenarioB->getUid(), $startingConversations[1]->getScenario()->getUid());
//    }
//
//    public function testGetAllConversationsInScenarios() {
//        /**
//         * Scenario A -> [Conversation A ]
//         * Scenario B -> [Conversation B , Conversation D]
//         * Scenario C -> []
//         */
//        $scenarioA = new Scenario();
//        $scenarioA->setOdId("scenario_a");
//        $scenarioA->setName("Scenario A");
//        $scenarioA->setStatus(Scenario::LIVE_STATUS);
//        $scenarioA->setActive(true);
//        $scenarioA = $this->client->addScenario($scenarioA);
//
//        $scenarioB = new Scenario();
//        $scenarioB->setOdId("scenario_b");
//        $scenarioB->setName("Scenario B");
//        $scenarioB->setStatus(Scenario::LIVE_STATUS);
//        $scenarioB->setActive(true);
//        $scenarioB = $this->client->addScenario($scenarioB);
//
//        $scenarioC = new Scenario();
//        $scenarioC->setOdId("scenario_c");
//        $scenarioC->setName("Scenario C");
//        $scenarioC->setStatus(Scenario::LIVE_STATUS);
//        $scenarioC->setActive(true);
//        $scenarioC = $this->client->addScenario($scenarioC);
//
//        $conversationA = new Conversation();
//        $conversationA->setOdId("conversation_a");
//        $conversationA->setName("Conversation A");
//        $conversationA->setScenario($scenarioA);
//        $conversationA = $this->client->addConversation($conversationA);
//
//        $conversationB = new Conversation();
//        $conversationB->setOdId("conversation_b");
//        $conversationB->setName("Conversation B");
//        $conversationB->setScenario($scenarioB);
//        $conversationB = $this->client->addConversation($conversationB);
//
//        $conversationD = new Conversation();
//        $conversationD->setOdId("conversation_d");
//        $conversationD->setName("Conversation D");
//        $conversationD->setScenario($scenarioB);
//        $conversationD = $this->client->addConversation($conversationD);
//
//        $conversations = $this->client->getAllConversations(new ScenarioCollection([$scenarioA, $scenarioB,
//            $scenarioC]), false);
//        $this->assertEquals(3, $conversations->count());
//        $this->assertEquals($conversationA->getUid(), $conversations[0]->getUid());
//        $this->assertEquals($scenarioA->getUid(), $conversations[0]->getScenario()->getUid());
//        $this->assertEquals($conversationB->getUid(), $conversations[1]->getUid());
//        $this->assertEquals($scenarioB->getUid(), $conversations[1]->getScenario()->getUid());
//        $this->assertEquals($conversationD->getUid(), $conversations[2]->getUid());
//        $this->assertEquals($scenarioB->getUid(), $conversations[2]->getScenario()->getUid());
//    }
//

}
