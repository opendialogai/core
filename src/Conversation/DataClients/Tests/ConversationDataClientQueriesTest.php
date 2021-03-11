<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;


use DateTime;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\DataClients\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Exceptions\InsufficientHydrationException;
use OpenDialogAi\Core\Conversation\Scenario;
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

    public function testGetScenario()
    {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());

        $scenario = $this->client->getScenarioByUid($testScenario->getUid(), false);
        $this->assertNotNull($scenario->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenario->getOdId());
        $this->assertEquals($testScenario->getName(), $scenario->getName());
        $this->assertEquals($testScenario->isActive(), $scenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $scenario->getStatus());
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
        $this->assertEquals($testScenario->getCreatedAt(), $scenario->getCreatedAt());
        $this->assertEquals($testScenario->getUpdatedAt(), $scenario->getUpdatedAt());
        $this->assertEquals($testScenario->isActive(), $scenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $scenario->getStatus());
        $this->assertEquals($testScenario->getConversations(), $scenario->getConversations());
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
