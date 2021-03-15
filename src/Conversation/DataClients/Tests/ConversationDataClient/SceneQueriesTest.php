<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;


use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;

class SceneQueriesTest extends ConversationDataClientQueriesTest
{
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

    public function testGetScenesByConversation() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversationA = new Conversation();
        $conversationA->setOdId("scenario_a");
        $conversationA->setName("Scenario A");
        $conversationA->setScenario($scenario);

        $conversationB = new Conversation();
        $conversationB->setOdId("scenario_b");
        $conversationB->setName("Scenario B");
        $conversationB->setScenario($scenario);


        $conversationA = $this->client->addConversation($conversationA);
        $conversationB = $this->client->addConversation($conversationB);

        $sceneA = new Scene();
        $sceneA->setOdId("conversation_a");
        $sceneA->setName("Conversation A");
        $sceneA->setConversation($conversationA);

        $sceneB = new Scene();
        $sceneB->setOdId("conversation_b");
        $sceneB->setName("Conversation B");
        $sceneB->setConversation($conversationB);

        $sceneA = $this->client->addScene($sceneA);
        $sceneB = $this->client->addScene($sceneB);

        $scenesInConversationA = $this->client->getAllScenesByConversation($conversationA, false);
        $this->assertEquals(1, $scenesInConversationA->count());
        $this->assertEquals($sceneA->getUid(), $scenesInConversationA[0]->getUid());

        $scenesInConversationB = $this->client->getAllScenesByConversation($conversationB, false);
        $this->assertEquals(1, $scenesInConversationB->count());
        $this->assertEquals($sceneB->getUid(), $scenesInConversationB[0]->getUid());

    }

    public function testGetSceneByUid() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversation = $this->getStandaloneConversation();
        $conversation->setScenario($scenario);
        $conversation = $this->client->addConversation($conversation);


        $testScene = new Scene();
        $testScene->setOdId("test_scene");
        $testScene->setName("Test Scene");
        $testScene->setConversation($conversation);
        $testScene = $this->client->addScene($testScene);

        $scene = $this->client->getSceneByUid($testScene->getUid(), false);
        $this->assertNotNull($scene->getUid());
        $this->assertEquals($testScene->getOdId(), $scene->getOdId());
        $this->assertEquals($testScene->getName(), $scene->getName());
        $this->assertEquals(new TurnCollection(), $scene->getTurns());


    }

    public function testGetSceneNonExistandUid() {
        $this->expectException(ConversationObjectNotFoundException::class);
        $this->client->getSceneByUid("0x0001", false);
    }

    public function testUpdateScene() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversation = $this->getStandaloneConversation();
        $conversation->setScenario($scenario);
        $conversation = $this->client->addConversation($conversation);

        $testScene = new Scene();
        $testScene->setOdId("test_scene");
        $testScene->setName("Test Scene");
        $testScene->setConversation($conversation);
        $testScene = $this->client->addScene($testScene);

        $changes = new Scene();
        $changes->setUid($testScene->getUid());
        $changes->setName("Updated name");
        $changes->setOdId("updated_id");

        $updateScene = $this->client->updateScene($changes);
        $this->assertEquals($testScene->getUid(), $updateScene->getUid());
        $this->assertEquals($changes->getOdId(), $updateScene->getOdId());
        $this->assertEquals($changes->getName(), $updateScene->getName());
        $this->assertEquals($testScene->getDescription(), $updateScene->getDescription());
        $this->assertEquals($testScene->getBehaviors(), $updateScene->getBehaviors());
        $this->assertEquals($testScene->getConditions(), $updateScene->getConditions());
        $this->assertEquals($testScene->getInterpreter(), $updateScene->getInterpreter());
        $this->assertEquals($testScene->getCreatedAt(), $updateScene->getCreatedAt());
        $this->assertEquals(new TurnCollection(), $updateScene->getTurns());

    }

    public function testDeleteScene() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        $conversation = $this->getStandaloneConversation();
        $conversation->setScenario($scenario);
        $conversation = $this->client->addConversation($conversation);

        $testScene = new Scene();
        $testScene->setOdId("test_scene");
        $testScene->setName("Test Scene");
        $testScene->setConversation($conversation);
        $testScene = $this->client->addScene($testScene);

        $success = $this->client->deleteSceneByUid($testScene->getUid());
        //Todo: Check for deletion cascade.
        $this->assertEquals(true, $success);
    }

    public function testGetStartingScenesInConversations() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        /**
         * Conversation A -> [Scene A (STARTING)]
         * Conversation B -> [Scene B (COMPLETING), Scene D (COMPLETING,STARTING)]
         * Conversation C -> []
         */
        $conversationA = new Conversation();
        $conversationA->setOdId("conversation_a");
        $conversationA->setName("Conversation A");
        $conversationA->setScenario($scenario);
        $conversationA = $this->client->addConversation($conversationA);

        $conversationB = new Conversation();
        $conversationB->setOdId("conversation_b");
        $conversationB->setName("Conversation B");
        $conversationB->setScenario($scenario);
        $conversationB = $this->client->addConversation($conversationB);

        $conversationC = new Conversation();
        $conversationC->setOdId("scenario_c");
        $conversationC->setName("Conversation C");
        $conversationC->setScenario($scenario);
        $conversationC = $this->client->addConversation($conversationC);

        $sceneA = new Scene();
        $sceneA->setOdId("scene_a");
        $sceneA->setName("Scene A");
        $sceneA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
        $sceneA->setConversation($conversationA);
        $sceneA = $this->client->addScene($sceneA);

        $sceneB = new Scene();
        $sceneB->setOdId("scene_b");
        $sceneB->setName("Scene B");
        $sceneB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING)]));
        $sceneB->setConversation($conversationB);
        $sceneB = $this->client->addScene($sceneB);

        $sceneD = new Scene();
        $sceneD->setOdId("conversation_d");
        $sceneD->setName("Conversation D");
        $sceneD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::STARTING)]));
        $sceneD->setConversation($conversationB);
        $sceneD = $this->client->addScene($sceneD);

        $startingScenes = $this->client->getAllStartingScenes(new ConversationCollection([$conversationA, $conversationB,
            $conversationC]), false);
        $this->assertEquals(2, $startingScenes->count());
        $this->assertEquals($sceneA->getUid(), $startingScenes[0]->getUid());
        $this->assertEquals($conversationA->getUid(), $startingScenes[0]->getConversation()->getUid());
        $this->assertEquals($sceneD->getUid(), $startingScenes[1]->getUid());
        $this->assertEquals($conversationB->getUid(), $startingScenes[1]->getConversation()->getUid());
    }


    public function testGetOpeningScenesInConversations() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        /**
         * Conversation A -> [Scene A (OPEN)]
         * Conversation B -> [Scene B (STARTING), Scene D (COMPLETING,OPEN)]
         * Conversation C -> []
         */
        $conversationA = new Conversation();
        $conversationA->setOdId("conversation_a");
        $conversationA->setName("Conversation A");
        $conversationA->setScenario($scenario);
        $conversationA = $this->client->addConversation($conversationA);

        $conversationB = new Conversation();
        $conversationB->setOdId("conversation_b");
        $conversationB->setName("Conversation B");
        $conversationB->setScenario($scenario);
        $conversationB = $this->client->addConversation($conversationB);

        $conversationC = new Conversation();
        $conversationC->setOdId("scenario_c");
        $conversationC->setName("Conversation C");
        $conversationC->setScenario($scenario);
        $conversationC = $this->client->addConversation($conversationC);

        $sceneA = new Scene();
        $sceneA->setOdId("scene_a");
        $sceneA->setName("Scene A");
        $sceneA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN)]));
        $sceneA->setConversation($conversationA);
        $sceneA = $this->client->addScene($sceneA);

        $sceneB = new Scene();
        $sceneB->setOdId("scene_b");
        $sceneB->setName("Scene B");
        $sceneB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
        $sceneB->setConversation($conversationB);
        $sceneB = $this->client->addScene($sceneB);

        $sceneD = new Scene();
        $sceneD->setOdId("conversation_d");
        $sceneD->setName("Conversation D");
        $sceneD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::OPEN)]));
        $sceneD->setConversation($conversationB);
        $sceneD = $this->client->addScene($sceneD);

        $openingScenes = $this->client->getAllOpenScenes(new ConversationCollection([$conversationA, $conversationB,
            $conversationC]), false);
        $this->assertEquals(2, $openingScenes->count());
        $this->assertEquals($sceneA->getUid(), $openingScenes[0]->getUid());
        $this->assertEquals($conversationA->getUid(), $openingScenes[0]->getConversation()->getUid());
        $this->assertEquals($sceneD->getUid(), $openingScenes[1]->getUid());
        $this->assertEquals($conversationB->getUid(), $openingScenes[1]->getConversation()->getUid());
    }

    public function testGetAllScenesInConversations() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
        /**
         * Conversation A -> [Scene A]
         * Conversation B -> [Scene B , Scene D]
         * Conversation C -> []
         */
        $conversationA = new Conversation();
        $conversationA->setOdId("conversation_a");
        $conversationA->setName("Conversation A");
        $conversationA->setScenario($scenario);
        $conversationA = $this->client->addConversation($conversationA);

        $conversationB = new Conversation();
        $conversationB->setOdId("conversation_b");
        $conversationB->setName("Conversation B");
        $conversationB->setScenario($scenario);
        $conversationB = $this->client->addConversation($conversationB);

        $conversationC = new Conversation();
        $conversationC->setOdId("scenario_c");
        $conversationC->setName("Conversation C");
        $conversationC->setScenario($scenario);
        $conversationC = $this->client->addConversation($conversationC);

        $sceneA = new Scene();
        $sceneA->setOdId("scene_a");
        $sceneA->setName("Scene A");
        $sceneA->setConversation($conversationA);
        $sceneA = $this->client->addScene($sceneA);

        $sceneB = new Scene();
        $sceneB->setOdId("scene_b");
        $sceneB->setName("Scene B");
        $sceneB->setConversation($conversationB);
        $sceneB = $this->client->addScene($sceneB);

        $sceneD = new Scene();
        $sceneD->setOdId("conversation_d");
        $sceneD->setName("Conversation D");
        $sceneD->setConversation($conversationB);
        $sceneD = $this->client->addScene($sceneD);

        $scenes = $this->client->getAllScenes(new ConversationCollection([$conversationA, $conversationB,
            $conversationC]), false);
        $this->assertEquals(3, $scenes->count());
        $this->assertEquals($sceneA->getUid(), $scenes[0]->getUid());
        $this->assertEquals($conversationA->getUid(), $scenes[0]->getConversation()->getUid());
        $this->assertEquals($sceneB->getUid(), $scenes[1]->getUid());
        $this->assertEquals($conversationB->getUid(), $scenes[1]->getConversation()->getUid());
        $this->assertEquals($sceneD->getUid(), $scenes[2]->getUid());
        $this->assertEquals($conversationB->getUid(), $scenes[2]->getConversation()->getUid());

    }

}
