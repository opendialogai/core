<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;


use OpenDialogAi\Core\Conversation\Conversation;
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

        $scenarioTree = $this->client->getScenarioWithFocusedScene($testScene->getUid());
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
}
