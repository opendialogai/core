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
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Tests\TestCase;

class OutgoingIntentMatcherTest extends TestCase
{
    const TEST_SCENARIO_1 = 'test_scenario1';
    const TEST_CONVERSATION_1 = 'test_conversation1';
    const TEST_SCENE_1 = 'test_scene1';
    const TEST_TURN_1 = 'test_turn1';
    const TEST_INTENT_1_INPUT = 'test_intent1_input';
    const TEST_TURN_2 = 'test_turn2';
    const TEST_INTENT_2_OUTPUT = 'test_intent2_output';

    public function testNoMatchingIntents()
    {
        // Mock selectors, no response intents will be selected
        $intents = new IntentCollection();
        $this->mockSelectorsForOutgoingResponse($intents);

        // Set conversational state
        $this->updateStateToOngoingForResponses();

        $this->expectException(NoMatchingIntentsException::class);
        OutgoingIntentMatcher::matchOutgoingIntent();
    }

    public function testBasicAsResponseMatch()
    {
        // Mock selectors, a response intent will be selected
        $intent = new Intent();
        $intent->setODId('test_intent1');
        $intents = new IntentCollection([$intent]);
        $this->mockSelectorsForOutgoingResponse($intents);

        // Set conversational state
        $this->updateStateToOngoingForResponses();

        $this->assertSame($intent, OutgoingIntentMatcher::matchOutgoingIntent());
    }

    public function testOngoingAsRequestMatchWithOpenTurns()
    {
        // Mock selectors, a request intent will be selected
        $desiredIntent = $this->mockSelectorsForOutgoingOngoingOpenTurnRequest(self::TEST_INTENT_2_OUTPUT);

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        $this->assertSame($desiredIntent, OutgoingIntentMatcher::matchOutgoingIntent());
    }

    public function testOngoingAsRequestMatchWithValidOrigin()
    {
        // Mock selectors, a request intent will be selected
        $desiredIntent = $this->mockSelectorsForOutgoingOngoingValidOriginRequest(self::TEST_INTENT_2_OUTPUT);

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        $this->assertSame($desiredIntent, OutgoingIntentMatcher::matchOutgoingIntent());
    }

    private function updateStateToOngoingForResponses()
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
            $conversationContextId .'.'.Intent::INTENT_IS_REQUEST,
            true
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::USER
        );
    }

    private function updateStateToOngoingForRequests()
    {
        $conversationContextId = ConversationContext::getComponentId();

        ContextService::saveAttribute(
            $conversationContextId .'.'.Scenario::CURRENT_SCENARIO,
            self::TEST_SCENARIO_1
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Conversation::CURRENT_CONVERSATION,
            self::TEST_CONVERSATION_1
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Scene::CURRENT_SCENE,
            self::TEST_SCENE_1
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Turn::CURRENT_TURN,
            self::TEST_TURN_1
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_INTENT,
            self::TEST_INTENT_1_INPUT
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::INTENT_IS_REQUEST,
            false
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::USER
        );
    }

    /**
     * @param IntentCollection $intents
     */
    private function mockSelectorsForOutgoingResponse(IntentCollection $intents): void
    {
        $scenario = new Scenario();
        $scenario->setODId('test_scenario1');

        ScenarioSelector::shouldReceive('selectScenarioById')
            ->once()
            ->andReturn($scenario);

        $conversation = new Conversation();
        $conversation->setODId('test_conversation1');
        ConversationSelector::shouldReceive('selectConversationById')
            ->once()
            ->andReturn($conversation);

        $scene = new Scene();
        $scene->setODId('test_scene1');
        SceneSelector::shouldReceive('selectSceneById')
            ->once()
            ->andReturn($scene);

        $turn = new Turn();
        $turn->setODId('test_turn1');
        TurnSelector::shouldReceive('selectTurnById')
            ->once()
            ->andReturn($turn);

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->andReturn($intents);
    }

    /**
     * @return Scene
     */
    private function mockSelectorsForOngoing(): Scene
    {
        $scenario = new Scenario();
        $scenario->setODId(self::TEST_SCENARIO_1);

        ScenarioSelector::shouldReceive('selectScenarioById')
            ->once()
            ->andReturn($scenario);

        $conversation = new Conversation($scenario);
        $conversation->setODId(self::TEST_CONVERSATION_1);
        ConversationSelector::shouldReceive('selectConversationById')
            ->once()
            ->andReturn($conversation);

        $scene = new Scene($conversation);
        $scene->setODId(self::TEST_SCENE_1);
        SceneSelector::shouldReceive('selectSceneById')
            ->once()
            ->andReturn($scene);

        return $scene;
    }

    /**
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForOutgoingOngoingOpenTurnRequest(string $desiredIntentId): Intent
    {
        $scene = $this->mockSelectorsForOngoing();

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->once()
            ->andReturn(new TurnCollection());

        $intents = new IntentCollection();
        $desiredIntent = new Intent($turn, Intent::APP);
        $desiredIntent->setODId($desiredIntentId);
        $intents->addObject($desiredIntent);

        $undesiredIntent = new Intent($turn, Intent::APP);
        $undesiredIntent->setODId('test_undesired_intent');
        $intents->addObject($undesiredIntent);

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->andReturn($intents);

        return $desiredIntent;
    }

    /**
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForOutgoingOngoingValidOriginRequest(string $desiredIntentId): Intent
    {
        $scene = $this->mockSelectorsForOngoing();

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);
        $turn->setValidOrigins([self::TEST_INTENT_1_INPUT]);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        $intents = new IntentCollection();

        $desiredIntent = new Intent($turn, Intent::USER);
        $desiredIntent->setODId($desiredIntentId);
        $intents->addObject($desiredIntent);

        $undesiredIntent = new Intent($turn, Intent::USER);
        $undesiredIntent->setODId('test_undesired_intent');
        $intents->addObject($undesiredIntent);

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->withArgs(function ($turns) {
                // We only have one turn, but it is both an open turn and one with a matching valid origin
                // so we should check it's not duplicated
                return $turns instanceof TurnCollection && count($turns) === 1;
            })
            ->andReturn($intents);

        return $desiredIntent;
    }
}
