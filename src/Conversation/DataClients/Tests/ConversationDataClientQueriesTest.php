<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;


use DateTime;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\DataClients\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

class ConversationDataClientQueriesTest extends TestCase
{
    protected ConversationDataClient $client;
    public function resetGraphQL() {
        $client = resolve(GraphQLClientInterface::class);
        $client->dropAll();
        $client->setSchema(config('opendialog.graphql.schema'));
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->resetGraphQL();
        $this->client = resolve(ConversationDataClient::class);
    }

    public function getStandaloneScenario() {
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

    public function testGetAllScenarios() {
        $testScenario = $this->getStandaloneScenario();
        $this->client->addScenario($testScenario);

        $scenarios = $this->client->getAllScenarios(false);
        $this->assertEquals(1,$scenarios->count());
        $scenario = $scenarios[0];
        $this->assertNotNull($scenario->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenario->getOdId());
        $this->assertEquals($testScenario->getName(), $scenario->getName());
        $this->assertEquals($testScenario->isActive(), $scenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $scenario->getStatus());

    }

    public function testGetScenario() {
        $testScenario = $this->client->addScenario($this->getStandaloneScenario());

        $scenario = $this->client->getScenarioByUid($testScenario->getUid(), false);
        $this->assertNotNull($scenario->getUid());
        $this->assertEquals($testScenario->getOdId(), $scenario->getOdId());
        $this->assertEquals($testScenario->getName(), $scenario->getName());
        $this->assertEquals($testScenario->isActive(), $scenario->isActive());
        $this->assertEquals($testScenario->getStatus(), $scenario->getStatus());

    }


    public function testAddScenario() {
        $testScenario = $this->getStandaloneScenario();
        $scenario = $this->client->addScenario($this->getStandaloneScenario());
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

    public function testDeleteScenario() {
        $scenario = $this->client->addScenario($this->getStandaloneScenario());

        $success =  $this->client->deleteScenarioByUid($scenario->getUid());
        $this->assertEquals(true, $success);
    }

    public function testUpdateScenario() {
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

}
