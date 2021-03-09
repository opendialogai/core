<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\ConversationEngine\Reasoners\IncomingIntentMatcher;
use OpenDialogAi\ConversationEngine\Reasoners\OutgoingIntentMatcher;
use OpenDialogAi\ConversationEngine\Util\ConversationContextUtil;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
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

class ConversationEngineTest extends TestCase
{
    const TEST_SCENARIO_1 = 'test_scenario1';
    const TEST_CONVERSATION_1 = 'test_conversation1';
    const TEST_SCENE_1 = 'test_scene1';
    const TEST_TURN_1 = 'test_turn1';
    const TEST_INTENT_1_OUTPUT = 'test_turn1_output';
    const TEST_TURN_2 = 'test_turn2';

    /**
     * Tests the case in which we have a new user (or a user that has completed a conversation previously) and a turn
     * with an incoming request intent, and an outgoing response intent that has the completing behaviour. This
     * represents the most basic, atomic kind of conversation a user may have with an application. The test asserts that
     * it is possible to do this twice consecutively, to ensure that the state inbetween the two attempts is suitable
     * for subsequent use.
     */
    public function testNonOngoingSingleTurnMatchingThatCompletes()
    {
        $this->updateStateToUndefined();
        $this->assertNonOngoingSingleTurnMatchingThatCompletes(true);
        $this->assertNonOngoingSingleTurnMatchingThatCompletes(false);
    }

    /**
     * Test the case in which we have a user who previously received an outgoing intent that was a turn response, and a
     * turn with open behaviour and an incoming request intent.
     */
    public function testOngoingCrossTurnMatchingOfIncomingIntentsViaOpenBehaviour()
    {
        // Place the user at the end of test_turn1
        $this->updateStateToOngoingForRequests();

        /* Matching the incoming intent */

        $expectedIncomingIntentId = 'test_intent2_input';
        $this->mockSelectorsForIncomingOngoingOpenTurnRequest($expectedIncomingIntentId);

        // Match incoming intent and update state
        $incomingIntent = IncomingIntentMatcher::matchIncomingIntent();
        ConversationEngine::updateState($incomingIntent);

        // The state should reflect the current intent, which is necessary for outgoing intent matching
        $this->assertEquals(self::TEST_SCENARIO_1, ConversationContextUtil::currentScenarioId());
        $this->assertEquals(self::TEST_CONVERSATION_1, ConversationContextUtil::currentConversationId());
        $this->assertEquals(self::TEST_SCENE_1, ConversationContextUtil::currentSceneId());
        $this->assertEquals(self::TEST_TURN_2, ConversationContextUtil::currentTurnId());
        $this->assertEquals($expectedIncomingIntentId, ConversationContextUtil::currentIntentId());
        $this->assertEquals(true, ConversationContextUtil::currentIntentIsRequest());
        $this->assertEquals(Intent::USER, ConversationContextUtil::currentSpeaker());

        /* Matching the outgoing intent */

        $expectedOutgoingIntentId = 'test_intent2_output';
        $this->mockSelectorsForOutgoingResponse($expectedOutgoingIntentId);

        // Match outgoing intent and update state
        $outgoingIntent = OutgoingIntentMatcher::matchOutgoingIntent();
        ConversationEngine::updateState($outgoingIntent);

        // The state is left in tact as the intent was not completing
        $this->assertEquals(self::TEST_SCENARIO_1, ConversationContextUtil::currentScenarioId());
        $this->assertEquals(self::TEST_CONVERSATION_1, ConversationContextUtil::currentConversationId());
        $this->assertEquals(self::TEST_SCENE_1, ConversationContextUtil::currentSceneId());
        $this->assertEquals(self::TEST_TURN_2, ConversationContextUtil::currentTurnId());
        $this->assertEquals($expectedOutgoingIntentId, ConversationContextUtil::currentIntentId());
        $this->assertEquals(false, ConversationContextUtil::currentIntentIsRequest());
        $this->assertEquals(Intent::APP, ConversationContextUtil::currentSpeaker());
    }

    /**
     * Test the case in which we have a user who previously received an outgoing intent that was a turn response, and a
     * turn with a matching valid origin and an incoming request intent.
     */
    public function testOngoingCrossTurnMatchingOfOutgoingIntentsViaValidOrigin()
    {
        // Place the user at the end of test_turn1
        $this->updateStateToOngoingForRequests();

        /* Matching the incoming intent */

        $expectedIncomingIntentId = 'test_intent2_input';
        $this->mockSelectorsForIncomingOngoingValidOriginTurnRequest($expectedIncomingIntentId);

        // Match incoming intent and update state
        $incomingIntent = IncomingIntentMatcher::matchIncomingIntent();
        ConversationEngine::updateState($incomingIntent);

        // The state should reflect the current intent, which is necessary for outgoing intent matching
        $this->assertEquals(self::TEST_SCENARIO_1, ConversationContextUtil::currentScenarioId());
        $this->assertEquals(self::TEST_CONVERSATION_1, ConversationContextUtil::currentConversationId());
        $this->assertEquals(self::TEST_SCENE_1, ConversationContextUtil::currentSceneId());
        $this->assertEquals(self::TEST_TURN_2, ConversationContextUtil::currentTurnId());
        $this->assertEquals($expectedIncomingIntentId, ConversationContextUtil::currentIntentId());
        $this->assertEquals(true, ConversationContextUtil::currentIntentIsRequest());
        $this->assertEquals(Intent::USER, ConversationContextUtil::currentSpeaker());

        /* Matching the outgoing intent */

        $expectedOutgoingIntentId = 'test_intent2_output';
        $this->mockSelectorsForOutgoingResponse($expectedOutgoingIntentId);

        // Match outgoing intent and update state
        $outgoingIntent = OutgoingIntentMatcher::matchOutgoingIntent();
        ConversationEngine::updateState($outgoingIntent);

        // The state is left in tact as the intent was not completing
        $this->assertEquals(self::TEST_SCENARIO_1, ConversationContextUtil::currentScenarioId());
        $this->assertEquals(self::TEST_CONVERSATION_1, ConversationContextUtil::currentConversationId());
        $this->assertEquals(self::TEST_SCENE_1, ConversationContextUtil::currentSceneId());
        $this->assertEquals(self::TEST_TURN_2, ConversationContextUtil::currentTurnId());
        $this->assertEquals($expectedOutgoingIntentId, ConversationContextUtil::currentIntentId());
        $this->assertEquals(false, ConversationContextUtil::currentIntentIsRequest());
        $this->assertEquals(Intent::APP, ConversationContextUtil::currentSpeaker());
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
            $conversationContextId .'.'.Intent::INTENT_IS_REQUEST,
            false
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::UNDEFINED
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
            self::TEST_INTENT_1_OUTPUT
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::INTENT_IS_REQUEST,
            false
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::APP
        );
    }

    /**
     * @param string $expectedIntentId
     * @param bool $shouldMatchScenario
     */
    private function mockSelectorsForIncomingStartingRequest(string $expectedIntentId, bool $shouldMatchScenario): void
    {
        $scenario = new Scenario();
        $scenario->setODId(self::TEST_SCENARIO_1);

        if ($shouldMatchScenario) {
            ScenarioSelector::shouldReceive('selectScenarios')
                ->once()
                ->andReturn(new ScenarioCollection([$scenario]));
        } else {
            ScenarioSelector::shouldReceive('selectScenarioById')
                ->once()
                ->andReturn($scenario);
        }

        $conversation = new Conversation($scenario);
        $conversation->setODId(self::TEST_CONVERSATION_1);
        ConversationSelector::shouldReceive('selectStartingConversations')
            ->once()
            ->andReturn(new ConversationCollection([$conversation]));

        $scene = new Scene($conversation);
        $scene->setODId(self::TEST_SCENE_1);
        SceneSelector::shouldReceive('selectStartingScenes')
            ->once()
            ->andReturn(new SceneCollection([$scene]));

        $turn = new Turn($scene);
        $turn->setODId(self::TEST_TURN_1);
        TurnSelector::shouldReceive('selectStartingTurns')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        $intents = new IntentCollection();

        $intent = new Intent($turn, Intent::USER);
        $intent->setIsRequestIntent(true);
        $intent->setODId($expectedIntentId);
        $intents->addObject($intent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->andReturn($intents);
    }

    /**
     * @param string $expectedIntentId
     */
    private function mockSelectorsForOutgoingResponseThatCompletes(string $expectedIntentId): void
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

        $turn = new Turn($scene);
        $turn->setODId(self::TEST_TURN_1);
        TurnSelector::shouldReceive('selectTurnById')
            ->once()
            ->andReturn($turn);

        $intents = new IntentCollection();

        $intent = new Intent($turn, Intent::APP);
        $intent->setIsRequestIntent(false);
        $intent->setODId($expectedIntentId);
        $intent->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::COMPLETING_BEHAVIOR)]));
        $intents->addObject($intent);

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->andReturn($intents);
    }

    /**
     * @param string $expectedIntentId
     */
    private function mockSelectorsForOutgoingResponse(string $expectedIntentId): void
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

        $turn = new Turn($scene);
        $turn->setODId(self::TEST_TURN_2);
        TurnSelector::shouldReceive('selectTurnById')
            ->once()
            ->andReturn($turn);

        $intents = new IntentCollection();

        $intent = new Intent($turn, Intent::APP);
        $intent->setIsRequestIntent(false);
        $intent->setODId($expectedIntentId);
        $intents->addObject($intent);

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->andReturn($intents);
    }

    /**
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingOpenTurnRequest(string $desiredIntentId): Intent
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

        $desiredIntent = new Intent($turn, Intent::USER);
        $desiredIntent->setIsRequestIntent(true);
        $desiredIntent->setODId($desiredIntentId);
        $intents->addObject($desiredIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->andReturn($intents);

        return $desiredIntent;
    }

    /**
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingValidOriginTurnRequest(string $desiredIntentId): Intent
    {
        $scene = $this->mockSelectorsForOngoing();

        $turn = new Turn($scene);
        $turn->setODId(self::TEST_TURN_2);
        $turn->setValidOrigins([self::TEST_INTENT_1_OUTPUT]);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->once()
            ->andReturn(new TurnCollection());

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        $intents = new IntentCollection();

        $desiredIntent = new Intent($turn, Intent::USER);
        $desiredIntent->setIsRequestIntent(true);
        $desiredIntent->setODId($desiredIntentId);
        $intents->addObject($desiredIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->andReturn($intents);

        return $desiredIntent;
    }

    private function assertNonOngoingSingleTurnMatchingThatCompletes(bool $shouldMatchScenario): void
    {
        /* Matching the incoming intent */

        $expectedIncomingIntentId = 'test_intent1_input';
        $this->mockSelectorsForIncomingStartingRequest($expectedIncomingIntentId, $shouldMatchScenario);

        // Match incoming intent and update state
        $incomingIntent = IncomingIntentMatcher::matchIncomingIntent();
        ConversationEngine::updateState($incomingIntent);

        // The state should reflect the current intent, which is necessary for outgoing intent matching
        $this->assertEquals(self::TEST_SCENARIO_1, ConversationContextUtil::currentScenarioId());
        $this->assertEquals(self::TEST_CONVERSATION_1, ConversationContextUtil::currentConversationId());
        $this->assertEquals(self::TEST_SCENE_1, ConversationContextUtil::currentSceneId());
        $this->assertEquals(self::TEST_TURN_1, ConversationContextUtil::currentTurnId());
        $this->assertEquals($expectedIncomingIntentId, ConversationContextUtil::currentIntentId());
        $this->assertEquals(true, ConversationContextUtil::currentIntentIsRequest());
        $this->assertEquals(Intent::USER, ConversationContextUtil::currentSpeaker());

        /* Matching the outgoing intent */

        $expectedOutgoingIntentId = 'test_intent1_output';
        $this->mockSelectorsForOutgoingResponseThatCompletes($expectedOutgoingIntentId);

        // Match outgoing intent and update state
        $outgoingIntent = OutgoingIntentMatcher::matchOutgoingIntent();
        ConversationEngine::updateState($outgoingIntent);

        // Most of the state should be undefined as the current intent was completing
        $this->assertEquals(self::TEST_SCENARIO_1, ConversationContextUtil::currentScenarioId());
        $this->assertEquals(Conversation::UNDEFINED, ConversationContextUtil::currentConversationId());
        $this->assertEquals(Scene::UNDEFINED, ConversationContextUtil::currentSceneId());
        $this->assertEquals(Turn::UNDEFINED, ConversationContextUtil::currentTurnId());
        $this->assertEquals(Intent::UNDEFINED, ConversationContextUtil::currentIntentId());
        $this->assertEquals(false, ConversationContextUtil::currentIntentIsRequest());
        $this->assertEquals(Intent::APP, ConversationContextUtil::currentSpeaker());
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
}
