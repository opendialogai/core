<?php


namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\ConversationEngine\Reasoners\IncomingIntentMatcher;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Tests\TestCase;

class IncomingIntentMatcherTest extends TestCase
{
    public function testNoMatchingIntents()
    {
        // Mock selectors, no request intents will be selected
        $intents = new IntentCollection();
        $this->mockSelectors($intents);

        // Set conversational state
        $this->updateStateToUndefined();

        $this->expectException(NoMatchingIntentsException::class);
        IncomingIntentMatcher::matchIncomingIntent();
    }

    public function testBasicAsRequestMatch()
    {
        // Mock selectors, a request intent will be selected
        $intent = new Intent();
        $intent->setODId('test_intent1');
        $intents = new IntentCollection([$intent]);
        $this->mockSelectors($intents);

        // Set conversational state
        $this->updateStateToUndefined();

        $this->assertSame($intent, IncomingIntentMatcher::matchIncomingIntent());
    }

    private function updateStateToUndefined()
    {
        $conversationContextId = ConversationContext::getComponentId();

        ContextService::saveAttribute(
            $conversationContextId .'.'.Scenario::CURRENT_SCENARIO,
            Scenario::UNDEFINED
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Conversation::CURRENT_CONVERSATION,
            Conversation::UNDEFINED
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Scene::CURRENT_SCENE,
            Scene::UNDEFINED
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Turn::CURRENT_TURN,
            Turn::UNDEFINED
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_INTENT,
            Intent::UNDEFINED
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::UNDEFINED
        );
    }

    /**
     * @param IntentCollection $intents
     */
    private function mockSelectors(IntentCollection $intents): void
    {
        $scenario = new Scenario();
        $scenario->setODId('test_scenario1');

        ScenarioSelector::shouldReceive('selectScenarios')
            ->once()
            ->andReturn(new ScenarioCollection([$scenario]));

        $conversation = new Conversation();
        $conversation->setODId('test_conversation1');
        ConversationSelector::shouldReceive('selectStartingConversations')
            ->once()
            ->andReturn(new ConversationCollection([$conversation]));

        $scene = new Scene();
        $scene->setODId('test_scene1');
        SceneSelector::shouldReceive('selectStartingScenes')
            ->once()
            ->andReturn(new SceneCollection([$scene]));

        $turn = new Turn();
        $turn->setODId('test_turn1');
        TurnSelector::shouldReceive('selectStartingTurns')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->andReturn($intents);
    }
}
