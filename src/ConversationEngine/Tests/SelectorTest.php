<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Reasoners\IntentSelector;
use OpenDialogAi\ConversationEngine\Reasoners\ScenarioSelector;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Tests\ConversationGenerator;
use OpenDialogAi\Core\Conversation\Tests\IntentGenerator;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Tests\TestCase;


class SelectorTest extends TestCase
{
    public function testScenarioSelectorSelectScenariosShallow()
    {
        // TODO: Create shallow generator
        ConversationDataClient::shouldReceive('getAllActiveScenarios')
            ->once()
            ->andReturn(ConversationGenerator::generateScenariosWithEverything('test'), 1);

        /** @var Scenario[]|ScenarioCollection $shallowScenarios */
        $shallowScenarios = ScenarioSelector::selectScenarios(true);

        // There were four scenarios, but one was filtered out by conditions and another was deactivated
        $this->assertCount(2, $shallowScenarios);

        // We requested shallow scenarios so they shouldn't have references to other conversation objects
        $this->assertCount(0, $shallowScenarios[0]->getConversations());
        $this->assertCount(0, $shallowScenarios[1]->getConversations());
    }

    public function testScenarioSelectorSelectScenariosNonShallow()
    {
        ConversationDataClient::shouldReceive('getAllActiveScenarios')
            ->once()
            ->andReturn(ConversationGenerator::generateScenariosWithEverything('test'), 1);

        /** @var Scenario[]|ScenarioCollection $shallowScenarios */
        $shallowScenarios = ScenarioSelector::selectScenarios(false);

        // There were four scenarios, but one was filtered out by conditions and another was deactivated
        $this->assertCount(2, $shallowScenarios);

        // We requested non-shallow scenarios so they should have references to other conversation objects
        $this->assertCount(1, $shallowScenarios[0]->getConversations());
        $this->assertCount(1, $shallowScenarios[1]->getConversations());
    }

    public function testIntentSelectorSelectStartingIntentsNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        IntentSelector::selectStartingIntents(new TurnCollection());
    }

    public function testIntentSelectorSelectStartingIntentsShallow()
    {
        // TODO: Create shallow generator
        ConversationDataClient::shouldReceive('getAllStartingIntents')
            ->once()
            ->andReturn(IntentGenerator::generateIntents());

        // TODO: Generate shallow turns with conditions
        $turns = new TurnCollection();

        /** @var Intent[]|IntentCollection $shallowIntents */
        $shallowIntents = IntentSelector::selectStartingIntents($turns, true);

        // There were three intents, but one was filtered out by conditions and another was not starting
        $this->assertCount(2, $shallowIntents);

        // We requested shallow intents so they shouldn't have references to other conversation objects
        $this->assertNull($shallowIntents[0]->getTurn());
        $this->assertNull($shallowIntents[1]->getTurn());
    }
}
