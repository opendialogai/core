<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;


use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class FocusedQueriesTest extends ConversationDataClientQueriesTest
{

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

        $conversationTree = $this->client->getScenarioWithFocusedConversation($conversation->getUid());

        $scenarioTree = $conversationTree->getScenario();
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

    public function testGetScenarioWithFocusedScene() {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());
        $testConversation = $this->getStandaloneConversation();
        $testConversation->setScenario($testScenario);
        $testConversation = $this->client->addConversation($testConversation);

        $testScene = new Scene();
        $testScene->setOdId("test_scene");
        $testScene->setName("Test Scene");
        $testScene->setConversation($testConversation);
        $testScene = $this->client->addScene($testScene);

        $turnA = new Turn();
        $turnA->setOdId("turn_a");
        $turnA->setName("Turn A");
        $turnA->setScene($testScene);
        $turnA = $this->client->addTurn($turnA);

        $turnB = new Turn();
        $turnB->setOdId("scene_b");
        $turnB->setName("Scene B");
        $turnB->setScene($testScene);
        $turnB = $this->client->addTurn($turnB);

        $sceneTree = $this->client->getScenarioWithFocusedScene($testScene->getUid());
        $scenarioTree = $sceneTree->getScenario();

        $this->assertEquals($testScenario->getUid(), $scenarioTree->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenarioTree->getOdId());
        $this->assertNotNull($scenarioTree->getConversations());
        $this->assertEquals(1, $scenarioTree->getConversations()->count());

        $conversationTree = $scenarioTree->getConversations()->first();
        $this->assertEquals($testConversation->getUid(), $conversationTree->getUid());
        $this->assertEquals($testConversation->getOdId(), $conversationTree->getOdId());
        $this->assertEquals($testConversation->getName(), $conversationTree->getName());
        $this->assertNotNull($conversationTree->getScenes());
        $this->assertEquals(1, $conversationTree->getScenes()->count());
        $this->assertEquals($scenarioTree, $conversationTree->getScenario());

        $sceneTree = $conversationTree->getScenes()->first();
        $this->assertEquals($testScene->getUid(), $sceneTree->getUid());
        $this->assertEquals($testScene->getOdId(), $sceneTree->getOdId());
        $this->assertEquals($testScene->getName(), $sceneTree->getName());
        $this->assertNotNull($sceneTree->getTurns());
        $this->assertEquals(2, $sceneTree->getTurns()->count());
        $this->assertEquals($conversationTree, $sceneTree->getConversation());


        $turnATree = $sceneTree->getTurns()[0];
        $this->assertEquals($turnA->getUid(), $turnATree->getUid());
        $this->assertEquals($turnA->getOdId(), $turnATree->getOdId());
        $this->assertEquals($turnA->getName(), $turnATree->getName());
        $this->assertEquals($sceneTree, $turnATree->getScene());


        $turnBTree = $sceneTree->getTurns()[1];
        $this->assertEquals($turnB->getUid(), $turnBTree->getUid());
        $this->assertEquals($turnB->getOdId(), $turnBTree->getOdId());
        $this->assertEquals($turnB->getName(), $turnBTree->getName());
        $this->assertEquals($sceneTree, $turnBTree->getScene());

    }

    public function testGetScenarioWithFocusedTurnAndDependantTurns() {
        //TODO
    }

    public function testGetScenarioWithFocusedTurn() {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());
        $testConversation = $this->getStandaloneConversation();
        $testConversation->setScenario($testScenario);
        $testConversation = $this->client->addConversation($testConversation);
        $testScene = $this->getStandaloneScene();
        $testScene->setConversation($testConversation);
        $testScene = $this->client->addScene($testScene);

        $testTurn = new Turn();
        $testTurn->setOdId("test_turn");
        $testTurn->setName("Test Turn");
        $testTurn->setScene($testScene);
        $testTurn = $this->client->addTurn($testTurn);

        $intentA = new Intent();
        $intentA->setOdId("intent_a");
        $intentA->setName("Intent A");
        $intentA->setSpeaker(Intent::USER);
        $intentA->setConfidence(1.0);
        $intentA->setSampleUtterance("Intent A sample utterance");
        $intentA->setTurn($testTurn);
        $intentA = $this->client->addRequestIntent($intentA);

        $intentB = new Intent();
        $intentB->setOdId("intent_b");
        $intentB->setName("Intent B");
        $intentB->setSpeaker(Intent::USER);
        $intentB->setConfidence(1.0);
        $intentB->setSampleUtterance("Intent B sample utterance");
        $intentB->setTurn($testTurn);
        $intentB = $this->client->addResponseIntent($intentB);

        $intentC = new Intent();
        $intentC->setOdId("intent_c");
        $intentC->setName("Intent C");
        $intentC->setSpeaker(Intent::USER);
        $intentC->setConfidence(1.0);
        $intentC->setSampleUtterance("Intent C sample utterance");
        $intentC->setTurn($testTurn);
        $intentC = $this->client->addResponseIntent($intentC);

        $focusedTurn = $this->client->getScenarioWithFocusedTurn($testTurn->getUid());

        $scenarioTree = $focusedTurn->getScenario();

        $this->assertEquals($testScenario->getUid(), $scenarioTree->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenarioTree->getOdId());
        $this->assertNotNull($scenarioTree->getConversations());
        $this->assertEquals(1, $scenarioTree->getConversations()->count());

        $conversationTree = $scenarioTree->getConversations()->first();
        $this->assertEquals($testConversation->getUid(), $conversationTree->getUid());
        $this->assertEquals($testConversation->getOdId(), $conversationTree->getOdId());
        $this->assertEquals($testConversation->getName(), $conversationTree->getName());
        $this->assertNotNull($conversationTree->getScenes());
        $this->assertEquals(1, $conversationTree->getScenes()->count());
        $this->assertEquals($scenarioTree, $conversationTree->getScenario());

        $sceneTree = $conversationTree->getScenes()->first();
        $this->assertEquals($testScene->getUid(), $sceneTree->getUid());
        $this->assertEquals($testScene->getOdId(), $sceneTree->getOdId());
        $this->assertEquals($testScene->getName(), $sceneTree->getName());
        $this->assertNotNull($sceneTree->getTurns());
        $this->assertEquals(1, $sceneTree->getTurns()->count());
        $this->assertEquals($conversationTree, $sceneTree->getConversation());

        $turnTree = $sceneTree->getTurns()->first();
        $this->assertEquals($testTurn->getUid(), $turnTree->getUid());
        $this->assertEquals($testTurn->getOdId(), $turnTree->getOdId());
        $this->assertEquals($testTurn->getName(), $turnTree->getName());
        $this->assertEquals(1, $turnTree->getRequestIntents()->count());

        $intentATree = $turnTree->getRequestIntents()->first();
        $this->assertEquals($intentA->getUid(), $intentATree->getUid());
        $this->assertEquals($intentA->getOdId(), $intentATree->getOdId());
        $this->assertEquals($intentA->getName(), $intentATree->getName());
        $this->assertEquals($turnTree, $intentATree->getTurn());

        $intentBTree = $turnTree->getResponseIntents()[0];
        $this->assertEquals($intentB->getUid(), $intentBTree->getUid());
        $this->assertEquals($intentB->getOdId(), $intentBTree->getOdId());
        $this->assertEquals($intentB->getName(), $intentBTree->getName());
        $this->assertEquals($turnTree, $intentBTree->getTurn());

        $intentCTree = $turnTree->getResponseIntents()[1];
        $this->assertEquals($intentC->getUid(), $intentCTree->getUid());
        $this->assertEquals($intentC->getOdId(), $intentCTree->getOdId());
        $this->assertEquals($intentC->getName(), $intentCTree->getName());
        $this->assertEquals($turnTree, $intentCTree->getTurn());

    }


    public function testGetScenarioWithFocusedIntent() {

    }
}
