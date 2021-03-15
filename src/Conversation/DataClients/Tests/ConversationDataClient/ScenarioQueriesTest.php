<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests\ConversationDataClient;


use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;
use OpenDialogAi\Core\Conversation\Scenario;

class ScenarioQueriesTest extends ConversationDataClientQueriesTest
{
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
}
