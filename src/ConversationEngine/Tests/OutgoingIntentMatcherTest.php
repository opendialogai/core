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
use OpenDialogAi\ConversationEngine\Reasoners\OutgoingIntentMatcher;
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

class OutgoingIntentMatcherTest extends TestCase
{
    public function testNoMatchingIntents()
    {
        // Mock selectors, no response intents will be selected
        $intents = new IntentCollection();
        $this->mockSelectors($intents);

        // Set conversational state
        $this->updateStateToAfterIncomingIntent();

        $this->expectException(NoMatchingIntentsException::class);
        OutgoingIntentMatcher::matchOutgoingIntent();
    }

    public function testBasicAsResponseMatch()
    {
        // Mock selectors, a response intent will be selected
        $intent = new Intent();
        $intent->setODId('test_intent1');
        $intents = new IntentCollection([$intent]);
        $this->mockSelectors($intents);

        // Set conversational state
        $this->updateStateToAfterIncomingIntent();

        $this->assertSame($intent, OutgoingIntentMatcher::matchOutgoingIntent());
    }

    private function updateStateToAfterIncomingIntent()
    {
        $conversationContextId = ConversationContext::getComponentId();

        ContextService::saveAttribute(
            $conversationContextId .'.'.Scenario::CURRENT_SCENARIO,
            'test_scenario1'
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Conversation::CURRENT_CONVERSATION,
            'test_conversation1'
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Scene::CURRENT_SCENE,
            'test_scene1'
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Turn::CURRENT_TURN,
            'test_turn1'
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_INTENT,
            'test_intent1'
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::USER
        );
    }

    /**
     * @param IntentCollection $intents
     */
    private function mockSelectors(IntentCollection $intents): void
    {
        $scenario = new Scenario();
        $scenario->setODId('test_scenario1');

        ScenarioSelector::shouldReceive('selectScenario')
            ->once()
            ->andReturn(new ScenarioCollection([$scenario]));

        $conversation = new Conversation();
        $conversation->setODId('test_conversation1');
        ConversationSelector::shouldReceive('selectConversation')
            ->once()
            ->andReturn(new ConversationCollection([$conversation]));

        $scene = new Scene();
        $scene->setODId('test_scene1');
        SceneSelector::shouldReceive('selectScene')
            ->once()
            ->andReturn(new SceneCollection([$scene]));

        $turn = new Turn();
        $turn->setODId('test_turn1');
        TurnSelector::shouldReceive('selectTurn')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->andReturn($intents);
    }
}
