<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;


use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;

class ConversationQueriesTest extends ConversationDataClientQueriesTest
{
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
         * Scenario B -> [Conversation B (COMPLETING), Conversation D (COMPLETING,STARTING)]
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

    public function testGetOpenConversationsInScenarios() {
        /**
         * Scenario A -> [Conversation A (OPEN)]
         * Scenario B -> [Conversation B (STARTING), Conversation D (COMPLETING,OPEN)]
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
        $conversationA->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN)]));
        $conversationA = $this->client->addConversation($conversationA);

        $conversationB = new Conversation();
        $conversationB->setOdId("conversation_b");
        $conversationB->setName("Conversation B");
        $conversationB->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::STARTING)]));
        $conversationB->setScenario($scenarioB);
        $conversationB = $this->client->addConversation($conversationB);

        $conversationD = new Conversation();
        $conversationD->setOdId("conversation_d");
        $conversationD->setName("Conversation D");
        $conversationD->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING), new Behavior(Behavior::OPEN)]));
        $conversationD->setScenario($scenarioB);
        $conversationD = $this->client->addConversation($conversationD);

        $startingConversations = $this->client->getAllOpenConversations(new ScenarioCollection([$scenarioA, $scenarioB,
            $scenarioC]), false);
        $this->assertEquals(2, $startingConversations->count());
        $this->assertEquals($conversationA->getUid(), $startingConversations[0]->getUid());
        $this->assertEquals($scenarioA->getUid(), $startingConversations[0]->getScenario()->getUid());
        $this->assertEquals($conversationD->getUid(), $startingConversations[1]->getUid());
        $this->assertEquals($scenarioB->getUid(), $startingConversations[1]->getScenario()->getUid());
    }

    public function testGetAllConversationsInScenarios() {
        /**
         * Scenario A -> [Conversation A ]
         * Scenario B -> [Conversation B , Conversation D]
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
        $conversationA = $this->client->addConversation($conversationA);

        $conversationB = new Conversation();
        $conversationB->setOdId("conversation_b");
        $conversationB->setName("Conversation B");
        $conversationB->setScenario($scenarioB);
        $conversationB = $this->client->addConversation($conversationB);

        $conversationD = new Conversation();
        $conversationD->setOdId("conversation_d");
        $conversationD->setName("Conversation D");
        $conversationD->setScenario($scenarioB);
        $conversationD = $this->client->addConversation($conversationD);

        $conversations = $this->client->getAllConversations(new ScenarioCollection([$scenarioA, $scenarioB,
            $scenarioC]), false);
        $this->assertEquals(3, $conversations->count());
        $this->assertEquals($conversationA->getUid(), $conversations[0]->getUid());
        $this->assertEquals($scenarioA->getUid(), $conversations[0]->getScenario()->getUid());
        $this->assertEquals($conversationB->getUid(), $conversations[1]->getUid());
        $this->assertEquals($scenarioB->getUid(), $conversations[1]->getScenario()->getUid());
        $this->assertEquals($conversationD->getUid(), $conversations[2]->getUid());
        $this->assertEquals($scenarioB->getUid(), $conversations[2]->getScenario()->getUid());
    }

}
