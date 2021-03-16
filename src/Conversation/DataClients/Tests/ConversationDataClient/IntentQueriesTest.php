<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;

use OpenDialogAi\Core\Conversation\ActionsCollection;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Conversation\VirtualIntentCollection;

class IntentQueriesTest extends ConversationDataClientQueriesTest
{

    public function getTestTurn(): Turn {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());

        $conversation = $this->getStandaloneConversation();
        $conversation->setScenario($scenario);
        $conversation = $this->client->addConversation($conversation);

        $scene = $this->getStandaloneScene();
        $scene->setConversation($conversation);
        $scene = $this->client->addScene($scene);

        $turn = $this->getStandaloneTurn();
        $turn->setScene($scene);
        $turn = $this->client->addTurn($turn);

        return $turn;
    }

    public function testAddRequestIntent() {
        $testTurn = $this->getTestTurn();

        $intent = new Intent();
        $intent->setOdId("test_intent");
        $intent->setName("Test Intent");
        $intent->setConfidence(1.0);
        $intent->setSampleUtterance("A test sample utterance");
        $intent->setSpeaker(Intent::USER);
        $intent->setTurn($testTurn);
        $savedIntent = $this->client->addRequestIntent($intent);

        $updatedTurn = $this->client->getTurnByUid($savedIntent->getTurn()->getUid(), false);

        $this->assertEquals($intent->getOdId(), $savedIntent->getOdId());
        $this->assertEquals($intent->getName(), $savedIntent->getName());
        $this->assertEquals($intent->getConfidence(), $savedIntent->getConfidence());
        $this->assertEquals($intent->getSpeaker(), $savedIntent->getSpeaker());
        $this->assertEquals($intent->getSampleUtterance(), $savedIntent->getSampleUtterance());
        $this->assertEquals([], $savedIntent->getListensFor());
        $this->assertEquals([], $savedIntent->getExpectedAttributes());
        $this->assertEquals(new VirtualIntentCollection(), $savedIntent->getVirtualIntents());
        $this->assertEquals(new ActionsCollection(), $savedIntent->getActions());
        //Todo: How to represent lack of a transition vs transition not loaded from graphql?
//        $this->assertEquals(new Transition(null,null,null), $newIntent->getTransition());
        $this->assertContains($savedIntent->getUid(),$updatedTurn->getRequestIntents()->map(fn($intent) => $savedIntent->getUid()));

    }

    public function testAddResponseIntent() {
        $testTurn = $this->getTestTurn();

        $intent = new Intent();
        $intent->setOdId("test_intent");
        $intent->setName("Test Intent");
        $intent->setConfidence(1.0);
        $intent->setSampleUtterance("A test sample utterance");
        $intent->setSpeaker(Intent::USER);
        $intent->setTurn($testTurn);
        $savedIntent = $this->client->addResponseIntent($intent);

        $updatedTurn = $this->client->getTurnByUid($savedIntent->getTurn()->getUid(), false);

        $this->assertEquals($intent->getOdId(), $savedIntent->getOdId());
        $this->assertEquals($intent->getName(), $savedIntent->getName());
        $this->assertEquals($intent->getConfidence(), $savedIntent->getConfidence());
        $this->assertEquals($intent->getSpeaker(), $savedIntent->getSpeaker());
        $this->assertEquals($intent->getSampleUtterance(), $savedIntent->getSampleUtterance());
        $this->assertEquals([], $savedIntent->getListensFor());
        $this->assertEquals([], $savedIntent->getExpectedAttributes());
        $this->assertEquals(new VirtualIntentCollection(), $savedIntent->getVirtualIntents());
        $this->assertEquals(new ActionsCollection(), $savedIntent->getActions());
        //Todo: How to represent lack of a transition vs transition not loaded from graphql?
        //        $this->assertEquals(new Transition(null,null,null), $newIntent->getTransition());
        $this->assertContains($savedIntent->getUid(),$updatedTurn->getResponseIntents()->map(fn($intent) => $savedIntent->getUid()));

    }


    public function testGetIntentByUid() {
        $turn = $this->getTestTurn();

        $testIntent = new Intent();
        $testIntent->setOdId("test_intent");
        $testIntent->setName("Test Intent");
        $testIntent->setSpeaker(Intent::USER);
        $testIntent->setConfidence(1.0);
        $testIntent->setSampleUtterance("A test sample utterance");
        $testIntent->setTurn($turn);
        $testIntent = $this->client->addRequestIntent($testIntent);


        $intent = $this->client->getIntentByUid($testIntent->getUid(), false);
        $this->assertNotNull($intent->getUid());
        $this->assertEquals($testIntent->getOdId(), $intent->getOdId());
        $this->assertEquals($testIntent->getName(), $intent->getName());
        $this->assertEquals($testIntent->getConfidence(), $intent->getConfidence());
        $this->assertEquals($testIntent->getSpeaker(), $intent->getSpeaker());
        $this->assertEquals($testIntent->getSampleUtterance(), $intent->getSampleUtterance());
        $this->assertEquals([], $intent->getListensFor());
        $this->assertEquals([], $intent->getExpectedAttributes());
        $this->assertEquals(new VirtualIntentCollection(), $intent->getVirtualIntents());
        $this->assertEquals(new ActionsCollection(), $intent->getActions());

    }


    public function testUpdateIntent() {
        $turn = $this->getTestTurn();

        $testIntent = new Intent();
        $testIntent->setOdId("test_intent");
        $testIntent->setName("Test Intent");
        $testIntent->setConfidence(1.0);
        $testIntent->setSampleUtterance("A test sample utterance");
        $testIntent->setInterpreter("interpreter.core.example");
        $testIntent->setSpeaker(Intent::USER);
        $testIntent->setTurn($turn);
        $testIntent = $this->client->addRequestIntent($testIntent);


        $changes = new Intent();
        $changes->setUid($testIntent->getUid());
        $changes->setName("Updated name");
        $changes->setOdId("updated_id");
        $changes->setConfidence(0.5);
        $changes->setSampleUtterance("Updated sample utterance");
        $changes->setSpeaker(Intent::APP);
        $updatedIntent = $this->client->updateIntent($changes);

        $this->assertEquals($testIntent->getUid(), $updatedIntent->getUid());
        $this->assertEquals($changes->getOdId(), $updatedIntent->getOdId());
        $this->assertEquals($changes->getName(), $updatedIntent->getName());
        $this->assertEquals($testIntent->getDescription(), $updatedIntent->getDescription());
        $this->assertEquals($testIntent->getBehaviors(), $updatedIntent->getBehaviors());
        $this->assertEquals($testIntent->getConditions(), $updatedIntent->getConditions());
        $this->assertEquals($testIntent->getInterpreter(), $updatedIntent->getInterpreter());
        $this->assertEquals($testIntent->getCreatedAt(), $updatedIntent->getCreatedAt());
        $this->assertEquals($changes->getConfidence(), $updatedIntent->getConfidence());
        $this->assertEquals($changes->getSpeaker(), $updatedIntent->getSpeaker());
        $this->assertEquals($changes->getSampleUtterance(), $updatedIntent->getSampleUtterance());
        $this->assertEquals([], $updatedIntent->getExpectedAttributes());
        $this->assertEquals([], $updatedIntent->getListensFor());
        $this->assertEquals(new ActionsCollection(), $updatedIntent->getActions());
    }


    public function testDeleteIntentInvalidId() {
        $this->expectException(ConversationObjectNotFoundException::class);
        $this->client->deleteIntentByUid("0x0001");
    }

    public function testDeleteIntent() {
        $turn = $this->getTestTurn();

        $testIntent = new Intent();
        $testIntent->setOdId("test_intent");
        $testIntent->setName("Test Intent");
        $testIntent->setConfidence(1.0);
        $testIntent->setSpeaker(Intent::USER);
        $testIntent->setSampleUtterance("A test sample utterance");
        $testIntent->setTurn($turn);
        $testIntent = $this->client->addRequestIntent($testIntent);

        $success = $this->client->deleteIntentByUid($testIntent->getUid());
        //Todo: Check for deletion cascade.
        $this->assertEquals(true, $success);
    }

//    public function testAddTurn() {
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        $scene = $this->getStandaloneScene();
//        $scene->setConversation($conversation);
//        $scene = $this->client->addScene($scene);
//
//        $testTurn = new Turn();
//        $testTurn->setOdId("test_turn");
//        $testTurn->setName("Test Turn");
//        $testTurn->setScene($scene);
//        $turn = $this->client->addTurn($testTurn);
//
//        $this->assertIsString($turn->getUid());
//        $this->assertEquals($testTurn->getOdId(), $turn->getOdId());
//        $this->assertEquals($testTurn->getName(), $turn->getName());
//        $this->assertEquals($testTurn->getScene()->getUid(), $turn->getScene()->getUid());
//        $this->assertEquals(new ConditionCollection(), $turn->getConditions());
//        $this->assertEquals(new BehaviorsCollection(), $turn->getBehaviors());
//        $this->assertEquals($testTurn->getInterpreter(), $turn->getInterpreter());
//        $this->assertEquals(new IntentCollection(), $turn->getRequestIntents());
//        $this->assertEquals(new IntentCollection(), $turn->getResponseIntents());
//        $this->assertEquals([], $turn->getValidOrigins());
//
//    }
//
//    public function testGetTurnsByScene() {
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        $sceneA = new Scene();
//        $sceneA->setOdId("scene_a");
//        $sceneA->setName("Scene A");
//        $sceneA->setConversation($conversation);
//        $sceneA = $this->client->addScene($sceneA);
//
//
//        $sceneB = new Scene();
//        $sceneB->setOdId("scene_b");
//        $sceneB->setName("Scene B");
//        $sceneB->setConversation($conversation);
//        $sceneB = $this->client->addScene($sceneB);
//
//        $turnA = new Turn();
//        $turnA->setOdId("conversation_a");
//        $turnA->setName("Conversation A");
//        $turnA->setScene($sceneA);
//        $turnA = $this->client->addTurn($turnA);
//
//        $turnB = new Turn();
//        $turnB->setOdId("conversation_b");
//        $turnB->setName("Conversation B");
//        $turnB->setScene($sceneB);
//        $turnB = $this->client->addTurn($turnB);
//
//        $turnsInSceneA = $this->client->getAllTurnsByScene($sceneA, false);
//        $this->assertEquals(1, $turnsInSceneA->count());
//        $this->assertEquals($turnA->getUid(), $turnsInSceneA[0]->getUid());
//
//        $turnsInSceneB = $this->client->getAllTurnsByScene($sceneB, false);
//        $this->assertEquals(1, $turnsInSceneB->count());
//        $this->assertEquals($turnB->getUid(), $turnsInSceneB[0]->getUid());
//
//    }
//
//    public function testGetTurnByUid() {
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//        $scene = $this->getStandaloneScene();
//        $scene->setConversation($conversation);
//        $scene = $this->client->addScene($scene);
//
//
//        $testTurn = new Turn();
//        $testTurn->setOdId("test_turn");
//        $testTurn->setName("Test Turn");
//        $testTurn->setScene($scene);
//        $testTurn = $this->client->addTurn($testTurn);
//
//        $turn = $this->client->getTurnByUid($testTurn->getUid(), false);
//        $this->assertNotNull($turn->getUid());
//        $this->assertEquals($testTurn->getOdId(), $turn->getOdId());
//        $this->assertEquals($testTurn->getName(), $turn->getName());
//        $this->assertEquals(new IntentCollection(), $turn->getRequestIntents());
//        $this->assertEquals(new IntentCollection(), $turn->getResponseIntents());
//        $this->assertEquals([], $turn->getValidOrigins());
//
//    }
//
//    public function testGetTurnNonExistantUid() {
//        $this->expectException(ConversationObjectNotFoundException::class);
//        $this->client->getTurnByUid("0x0001", false);
//    }
//


//
//    public function testGetTurnsByValidOrigin() {
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        /**
//         * Scene A -> [Turn A (origin_a)]
//         * Scene B -> [Turn B (origin_b), Turn C (origin_a)]
//         */
//
//        $sceneA = new Scene();
//        $sceneA->setOdId("scene_a");
//        $sceneA->setName("Scene A");
//        $sceneA->setConversation($conversation);
//        $sceneA = $this->client->addScene($sceneA);
//
//        $sceneB = new Scene();
//        $sceneB->setOdId("scene_b");
//        $sceneB->setName("Scene B");
//        $sceneB->setConversation($conversation);
//        $sceneB = $this->client->addScene($sceneB);
//
//
//        $turnA = new Turn();
//        $turnA->setName("turn_a");
//        $turnA->setOdId("Turn A");
//        $turnA->setValidOrigins(["origin_a"]);
//        $turnA->setScene($sceneA);
//        $turnA = $this->client->addTurn($turnA);
//
//        $turnB = new Turn();
//        $turnB->setName("turn_b");
//        $turnB->setOdId("Turn B");
//        $turnB->setValidOrigins(["origin_b"]);
//        $turnB->setScene($sceneB);
//        $turnB = $this->client->addTurn($turnB);
//
//
//        $turnC = new Turn();
//        $turnC->setName("turn_a");
//        $turnC->setOdId("Turn A");
//        $turnC->setValidOrigins(["origin_a"]);
//        $turnC->setScene($sceneB);
//        $turnC = $this->client->addTurn($turnC);
//
//        $turnsWithValidOriginA = $this->client->getAllTurnsByValidOrigin(new SceneCollection([$sceneA, $sceneB]), 'origin_a',
//            false);
//        $this->assertEquals(2, $turnsWithValidOriginA->count());
//        $this->assertEquals($turnA->getUid(), $turnsWithValidOriginA[0]->getUid());
//        $this->assertEquals($turnA->getOdId(), $turnsWithValidOriginA[0]->getOdId());
//        $this->assertEquals($turnA->getValidOrigins(), $turnsWithValidOriginA[0]->getValidOrigins());
//
//        $turnsWithValidOriginB = $this->client->getAllTurnsByValidOrigin(new SceneCollection([$sceneA, $sceneB]), 'origin_b',
//            false);
//        $this->assertEquals(1, $turnsWithValidOriginB->count());
//        $this->assertEquals($turnB->getUid(), $turnsWithValidOriginB[0]->getUid());
//        $this->assertEquals($turnB->getOdId(), $turnsWithValidOriginB[0]->getOdId());
//        $this->assertEquals($turnB->getValidOrigins(), $turnsWithValidOriginB[0]->getValidOrigins());
//
//
//    }
//
//    public function testGetStartingTurnsInScenes() {
//        /**
//         * Scene A -> [Turn A (STARTING)]
//         * Scene B -> [Turn B (COMPLETING), Turn D (COMPLETING,STARTING)]
//         * Scene C -> []
//         */
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        $sceneA = new Scene();
//        $sceneA->setOdId("scene_a");
//        $sceneA->setName("Scene A");
//        $sceneA->setConversation($conversation);
//        $sceneA = $this->client->addScene($sceneA);
//
//        $sceneB = new Scene();
//        $sceneB->setOdId("scene_b");
//        $sceneB->setName("Scene B");
//        $sceneB->setConversation($conversation);
//        $sceneB = $this->client->addScene($sceneB);
//
//        $sceneC = new Scene();
//        $sceneC->setOdId("scene_c");
//        $sceneC->setName("Scene C");
//        $sceneC->setConversation($conversation);
//        $sceneC = $this->client->addScene($sceneC);
//
//        $turnA = new Turn();
//        $turnA->setOdId("turn_a");
//        $turnA->setName("Turn A");
//        $turnA->setScene($sceneA);
//        $turnA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
//        $turnA = $this->client->addTurn($turnA);
//
//        $turnB = new Turn();
//        $turnB->setOdId("turn_b");
//        $turnB->setName("Turn B");
//        $turnB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING)]));
//        $turnB->setScene($sceneB);
//        $turnB = $this->client->addTurn($turnB);
//
//        $turnD = new Turn();
//        $turnD->setOdId("conversation_d");
//        $turnD->setName("Conversation D");
//        $turnD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::STARTING)]));
//        $turnD->setScene($sceneB);
//        $turnD = $this->client->addTurn($turnD);
//
//        $startingTurns = $this->client->getAllStartingTurns(new SceneCollection([$sceneA, $sceneB,
//            $sceneC]), false);
//        $this->assertEquals(2, $startingTurns->count());
//        $this->assertEquals($turnA->getUid(), $startingTurns[0]->getUid());
//        $this->assertEquals($sceneA->getUid(), $startingTurns[0]->getScene()->getUid());
//        $this->assertEquals($turnD->getUid(), $startingTurns[1]->getUid());
//        $this->assertEquals($sceneB->getUid(), $startingTurns[1]->getScene()->getUid());
//
//    }
//
//    public function testGetOpenTurnsInScenes() {
//        /**
//         * Scene A -> [Turn A (OPEN)]
//         * Scene B -> [Turn B (STARTING), Turn D (COMPLETING,OPEN)]
//         * Scene C -> []
//         */
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        $sceneA = new Scene();
//        $sceneA->setOdId("scene_a");
//        $sceneA->setName("Scene A");
//        $sceneA->setConversation($conversation);
//        $sceneA = $this->client->addScene($sceneA);
//
//        $sceneB = new Scene();
//        $sceneB->setOdId("scene_b");
//        $sceneB->setName("Scene B");
//        $sceneB->setConversation($conversation);
//        $sceneB = $this->client->addScene($sceneB);
//
//        $sceneC = new Scene();
//        $sceneC->setOdId("scene_c");
//        $sceneC->setName("Scene C");
//        $sceneC->setConversation($conversation);
//        $sceneC = $this->client->addScene($sceneC);
//
//        $turnA = new Turn();
//        $turnA->setOdId("turn_a");
//        $turnA->setName("Turn A");
//        $turnA->setScene($sceneA);
//        $turnA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN)]));
//        $turnA = $this->client->addTurn($turnA);
//
//        $turnB = new Turn();
//        $turnB->setOdId("turn_b");
//        $turnB->setName("Turn B");
//        $turnB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
//        $turnB->setScene($sceneB);
//        $turnB = $this->client->addTurn($turnB);
//
//        $turnD = new Turn();
//        $turnD->setOdId("conversation_d");
//        $turnD->setName("Conversation D");
//        $turnD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::OPEN)]));
//        $turnD->setScene($sceneB);
//        $turnD = $this->client->addTurn($turnD);
//
//        $openTurns = $this->client->getAllOpenTurns(new SceneCollection([$sceneA, $sceneB,
//            $sceneC]), false);
//        $this->assertEquals(2, $openTurns->count());
//        $this->assertEquals($turnA->getUid(), $openTurns[0]->getUid());
//        $this->assertEquals($sceneA->getUid(), $openTurns[0]->getScene()->getUid());
//        $this->assertEquals($turnD->getUid(), $openTurns[1]->getUid());
//        $this->assertEquals($sceneB->getUid(), $openTurns[1]->getScene()->getUid());
//
//    }
//
//    public function testGetAllTurnsInScenes() {
//        /**
//         * Scene A -> [Turn A ]
//         * Scene B -> [Turn B, Turn D]
//         * Scene C -> []
//         */
//        $scenario = $this->client->addScenario($this->getStandaloneScenario());
//        $conversation = $this->getStandaloneConversation();
//        $conversation->setScenario($scenario);
//        $conversation = $this->client->addConversation($conversation);
//
//        $sceneA = new Scene();
//        $sceneA->setOdId("scene_a");
//        $sceneA->setName("Scene A");
//        $sceneA->setConversation($conversation);
//        $sceneA = $this->client->addScene($sceneA);
//
//        $sceneB = new Scene();
//        $sceneB->setOdId("scene_b");
//        $sceneB->setName("Scene B");
//        $sceneB->setConversation($conversation);
//        $sceneB = $this->client->addScene($sceneB);
//
//        $sceneC = new Scene();
//        $sceneC->setOdId("scene_c");
//        $sceneC->setName("Scene C");
//        $sceneC->setConversation($conversation);
//        $sceneC = $this->client->addScene($sceneC);
//
//        $turnA = new Turn();
//        $turnA->setOdId("turn_a");
//        $turnA->setName("Turn A");
//        $turnA->setScene($sceneA);
//        $turnA = $this->client->addTurn($turnA);
//
//        $turnB = new Turn();
//        $turnB->setOdId("turn_b");
//        $turnB->setName("Turn B");
//        $turnB->setScene($sceneB);
//        $turnB = $this->client->addTurn($turnB);
//
//        $turnD = new Turn();
//        $turnD->setOdId("conversation_d");
//        $turnD->setName("Conversation D");
//        $turnD->setScene($sceneB);
//        $turnD = $this->client->addTurn($turnD);
//
//        $openTurns = $this->client->getAllTurns(new SceneCollection([$sceneA, $sceneB,
//            $sceneC]), false);
//        $this->assertEquals(3, $openTurns->count());
//        $this->assertEquals($turnA->getUid(), $openTurns[0]->getUid());
//        $this->assertEquals($sceneA->getUid(), $openTurns[0]->getScene()->getUid());
//        $this->assertEquals($turnB->getUid(), $openTurns[1]->getUid());
//        $this->assertEquals($sceneB->getUid(), $openTurns[1]->getScene()->getUid());
//        $this->assertEquals($turnD->getUid(), $openTurns[2]->getUid());
//        $this->assertEquals($sceneB->getUid(), $openTurns[2]->getScene()->getUid());
//
//    }

}
