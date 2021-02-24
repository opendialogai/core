<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Reasoners\IntentSelector;
use OpenDialogAi\ConversationEngine\Reasoners\ScenarioSelector;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\Tests\ConversationGenerator;
use OpenDialogAi\Core\Conversation\Tests\IntentGenerator;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;


class SelectorTest extends TestCase
{
    public function testScenarioSelector()
    {
        ConversationDataClient::shouldReceive('getAllActiveScenarios')->once()
            ->andReturn(ConversationGenerator::generateScenariosWithEverything('test'), 1);

        $conditionPassingScenarios = ScenarioSelector::selectActiveScenarios();
    }

    public function testIncomingIntentSelector()
    {
        $intents = ConversationDataClient::shouldReceive('getAllStartingIntents')->once()
            ->andReturn(IntentGenerator::generateIntents());

        $utterance = UtteranceGenerator::generateButtonResponseUtterance('intent.core.I3');

        ContextService::saveAttribute(
            'user'.'.'.UtteranceAttribute::UTTERANCE, $utterance
        );

        $matchingIntents = IntentSelector::selectStartingIntents(new TurnCollection());

        $this->assertCount(1, $matchingIntents);
        $this->assertEquals('intent.core.I3', $matchingIntents->pop()->getODId());
    }
}
