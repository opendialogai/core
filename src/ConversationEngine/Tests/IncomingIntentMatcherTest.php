<?php


namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\ContextEngine\Contexts\BaseContexts\ConversationContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Facades\Selectors\TurnSelector;
use OpenDialogAi\ConversationEngine\Reasoners\IncomingIntentMatcher;
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

class IncomingIntentMatcherTest extends TestCase
{
    const TEST_SCENARIO_1 = 'test_scenario1';
    const TEST_CONVERSATION_1 = 'test_conversation1';
    const TEST_SCENE_1 = 'test_scene1';
    const TEST_TURN_1 = 'test_turn1';
    const TEST_INTENT_1_INPUT = 'test_intent1_input';
    const TEST_INTENT_1_OUTPUT = 'test_intent1_output';
    const TEST_TURN_2 = 'test_turn2';
    const TEST_INTENT_2_INPUT = 'test_intent2_input';

    /**
     * Tests that an incoming intent is matched if there is not an ongoing conversation which would happen if an
     * outgoing intent has the completing behaviour, or if the user is completely new
     */
    public function testNonOngoingAsRequestMatch()
    {
        // Mock selectors, a request intent will be selected
        $intent = new Intent();
        $intent->setODId(self::TEST_INTENT_1_INPUT);
        $intents = new IntentCollection([$intent]);
        $this->mockSelectorsForIncomingStartingRequest($intents);

        // Set conversational state
        $this->updateStateToUndefined();

        $this->assertSame($intent, IncomingIntentMatcher::matchIncomingIntent());
    }

    /**
     * Tests that an incoming intent is matched via open turns if there was just an outgoing response intent matched,
     * which means we are looking to match an incoming intent as a new turn's request intent
     */
    public function testOngoingAsRequestMatchWithOpenTurns()
    {
        // Mock selectors, a request intent will be selected
        $desiredIntent = $this->mockSelectorsForIncomingOngoingOpenTurnRequest(self::TEST_INTENT_2_INPUT);

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        $this->assertSame($desiredIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    /**
     * Tests that an incoming intent is matched via valid origins if there was just an outgoing rsponse intent matched,
     * which means we are looking to match an incoming intent as a new turn's request intente
     */
    public function testOngoingAsRequestMatchWithValidOrigin()
    {
        // Mock selectors, a request intent will be selected
        $desiredIntent = $this->mockSelectorsForIncomingOngoingValidOriginRequest(self::TEST_INTENT_2_INPUT);

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        $this->assertSame($desiredIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    /**
     * Tests that an incoming intent is matched if there was just an outgoing request intent matched, which means we are
     * looking to match an incoming intent as the current turn's response intent
     */
    public function testOngoingAsResponseMatch()
    {
        // Mock selectors, a request intent will be selected
        $desiredIntent = $this->mockSelectorsForIncomingOngoingResponse(self::TEST_INTENT_1_INPUT);

        // Set conversational state
        $this->updateStateToOngoingForResponses();

        $this->assertSame($desiredIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    public function testGlobalNoMatchNoOngoingConversation()
    {
        // Mock selectors, no intents should match
        $noMatchIntent = new Intent();
        $noMatchIntent->setODId('intent.core.NoMatch');
        $this->mockSelectorsForIncomingStartingNoMatchRequest(new IntentCollection([$noMatchIntent]));

        // Set conversational state
        $this->updateStateToUndefined();

        // Assert no match intent
        $this->assertSame($noMatchIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    public function testTurnNoMatchOngoingAsRequest()
    {
        // Mock selectors, no intents should match
        $turnNoMatchIntent = $this->mockSelectorsForIncomingOngoingTurnNoMatchRequest();

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        // Assert no match intent
        $this->assertSame($turnNoMatchIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    public function testSceneNoMatchOngoingAsRequest()
    {
        // Mock selectors, no intents should match
        $sceneNoMatchIntent = $this->mockSelectorsForIncomingOngoingSceneNoMatchRequest();

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        // Assert no match intent
        $this->assertSame($sceneNoMatchIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    public function testConversationNoMatchOngoingAsRequest()
    {
        // Mock selectors, no intents should match
        $conversationNoMatchIntent = $this->mockSelectorsForIncomingOngoingConversationNoMatchRequest();

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        // Assert no match intent
        $this->assertSame($conversationNoMatchIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    public function testGlobalNoMatchOngoingAsRequest()
    {
        // Mock selectors, no intents should match
        $globalNoMatchIntent = $this->mockSelectorsForIncomingOngoingGlobalNoMatchRequest();

        // Set conversational state
        $this->updateStateToOngoingForRequests();

        // Assert no match intent
        $this->assertSame($globalNoMatchIntent, IncomingIntentMatcher::matchIncomingIntent());
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

    private function updateStateToOngoingForResponses()
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
            true
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::APP
        );
    }

    /**
     * @param IntentCollection $intents
     */
    private function mockSelectorsForIncomingStartingRequest(IntentCollection $intents): void
    {
        $scenario = new Scenario();
        $scenario->setODId(self::TEST_SCENARIO_1);

        ScenarioSelector::shouldReceive('selectScenarios')
            ->once()
            ->andReturn(new ScenarioCollection([$scenario]));

        $conversation = new Conversation();
        $conversation->setODId(self::TEST_CONVERSATION_1);
        ConversationSelector::shouldReceive('selectStartingConversations')
            ->once()
            ->andReturn(new ConversationCollection([$conversation]));

        $scene = new Scene();
        $scene->setODId(self::TEST_SCENE_1);
        SceneSelector::shouldReceive('selectStartingScenes')
            ->once()
            ->andReturn(new SceneCollection([$scene]));

        $turn = new Turn();
        $turn->setODId(self::TEST_TURN_1);
        TurnSelector::shouldReceive('selectStartingTurns')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->andReturn($intents);
    }

    /**
     * @param IntentCollection $noMatchIntents
     */
    private function mockSelectorsForIncomingStartingNoMatchRequest(IntentCollection $noMatchIntents): void
    {
        $scenario = new Scenario();
        $scenario->setODId(self::TEST_SCENARIO_1);

        ScenarioSelector::shouldReceive('selectScenarios')
            ->twice()
            ->andReturn(
                new ScenarioCollection([$scenario]),
                new ScenarioCollection([$scenario])
            );

        $conversation = new Conversation();
        $conversation->setODId(self::TEST_CONVERSATION_1);
        ConversationSelector::shouldReceive('selectStartingConversations')
            ->twice()
            ->andReturn(
                new ConversationCollection([$conversation]),
                new ConversationCollection([$conversation])
            );

        $scene = new Scene();
        $scene->setODId(self::TEST_SCENE_1);
        SceneSelector::shouldReceive('selectStartingScenes')
            ->twice()
            ->andReturn(
                new SceneCollection([$scene]),
                new SceneCollection([$scene])
            );

        $turn = new Turn();
        $turn->setODId(self::TEST_TURN_1);
        TurnSelector::shouldReceive('selectStartingTurns')
            ->twice()
            ->andReturn(
                new TurnCollection([$turn]),
                new TurnCollection([$turn])
            );

        IntentSelector::shouldReceive('selectRequestIntents')
            ->twice()
            ->andReturn(
                new IntentCollection(),
                $noMatchIntents
            );
    }

    /**
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingOpenTurnRequest(string $desiredIntentId): Intent
    {
        $scene = $this->createScene();

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

        $undesiredIntent = new Intent($turn, Intent::USER);
        $undesiredIntent->setODId('test_undesired_intent');
        $undesiredIntent->setConfidence(0.5);
        $undesiredIntentInterpreted = clone $undesiredIntent;
        $undesiredIntent->addInterpretedIntents(new IntentCollection([$undesiredIntentInterpreted]));
        $undesiredIntent->checkForMatch();
        $intents->addObject($undesiredIntent);

        $desiredIntent = new Intent($turn, Intent::USER);
        $desiredIntent->setODId($desiredIntentId);
        $desiredIntent->setConfidence(0.75);
        $desiredIntentInterpreted = clone $desiredIntent;
        $desiredIntent->addInterpretedIntents(new IntentCollection([$desiredIntentInterpreted]));
        $desiredIntent->checkForMatch();
        $intents->addObject($desiredIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->andReturn($intents);

        return $desiredIntent;
    }

    /**
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingTurnNoMatchRequest(): Intent
    {
        $scene = $this->createScene();

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->twice()
            ->andReturn(
                new TurnCollection([$turn]),
                new TurnCollection([$turn])
            );

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->twice()
            ->andReturn(
                new TurnCollection(),
                new TurnCollection(),
            );

        $intents = new IntentCollection();

        $noMatchIntent = new Intent($turn, Intent::USER);
        $noMatchIntent->setODId('intent.core.TurnNoMatch');
        $noMatchIntent->setConfidence(1);
        $desiredIntentInterpreted = clone $noMatchIntent;
        $noMatchIntent->addInterpretedIntents(new IntentCollection([$desiredIntentInterpreted]));
        $noMatchIntent->checkForMatch();
        $intents->addObject($noMatchIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->twice()
            ->andReturn(
                new IntentCollection(),
                $intents
            );

        return $noMatchIntent;
    }

    /**
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingSceneNoMatchRequest(): Intent
    {
        $scene = $this->createScene();

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->times(3)
            ->andReturn(
                new TurnCollection([$turn]),
                new TurnCollection([$turn]),
                new TurnCollection([$turn])
            );

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->times(3)
            ->andReturn(
                new TurnCollection(),
                new TurnCollection(),
                new TurnCollection()
            );

        $intents = new IntentCollection();

        $noMatchIntent = new Intent($turn, Intent::USER);
        $noMatchIntent->setODId('intent.core.SceneNoMatch');
        $noMatchIntent->setConfidence(1);
        $desiredIntentInterpreted = clone $noMatchIntent;
        $noMatchIntent->addInterpretedIntents(new IntentCollection([$desiredIntentInterpreted]));
        $noMatchIntent->checkForMatch();
        $intents->addObject($noMatchIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->times(3)
            ->andReturn(
                new IntentCollection(),
                new IntentCollection(),
                $intents
            );

        return $noMatchIntent;
    }

    /**
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingConversationNoMatchRequest(): Intent
    {
        $scene = $this->createScene();

        SceneSelector::shouldReceive('selectSceneById')
            ->times(3)
            ->andReturn(
                $scene,
                $scene,
                $scene
            );

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->times(3)
            ->andReturn(
                new TurnCollection([$turn]),
                new TurnCollection([$turn]),
                new TurnCollection([$turn])
            );

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->times(3)
            ->andReturn(
                new TurnCollection(),
                new TurnCollection(),
                new TurnCollection()
            );

        $intents = new IntentCollection();

        $noMatchIntent = new Intent($turn, Intent::USER);
        $noMatchIntent->setODId('intent.core.ConversationNoMatch');
        $noMatchIntent->setConfidence(1);
        $desiredIntentInterpreted = clone $noMatchIntent;
        $noMatchIntent->addInterpretedIntents(new IntentCollection([$desiredIntentInterpreted]));
        $noMatchIntent->checkForMatch();
        $intents->addObject($noMatchIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->times(4)
            ->andReturn(
                new IntentCollection(),
                new IntentCollection(),
                new IntentCollection(),
                new IntentCollection($intents)
            );

        $scenario = new Scenario();
        $scenario->setODId(self::TEST_SCENARIO_1);

        ScenarioSelector::shouldReceive('selectScenarioById')
            ->once()
            ->andReturn($scenario);

        $conversation = new Conversation();
        $conversation->setODId(self::TEST_CONVERSATION_1);
        ConversationSelector::shouldReceive('selectStartingConversations')
            ->once()
            ->andReturn(new ConversationCollection([$conversation]));

        $scene = new Scene();
        $scene->setODId(self::TEST_SCENE_1);
        SceneSelector::shouldReceive('selectStartingScenes')
            ->once()
            ->andReturn(new SceneCollection([$scene]));

        $turn = new Turn();
        $turn->setODId(self::TEST_TURN_1);
        TurnSelector::shouldReceive('selectStartingTurns')
            ->once()
            ->andReturn(new TurnCollection([$turn]));


        return $noMatchIntent;
    }

    /**
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingGlobalNoMatchRequest(): Intent
    {
        $scene = $this->createScene();

        SceneSelector::shouldReceive('selectSceneById')
            ->times(3)
            ->andReturn(
                $scene,
                $scene,
                $scene
            );

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->times(3)
            ->andReturn(
                new TurnCollection([$turn]),
                new TurnCollection([$turn]),
                new TurnCollection([$turn])
            );

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->times(3)
            ->andReturn(
                new TurnCollection(),
                new TurnCollection(),
                new TurnCollection()
            );

        $intents = new IntentCollection();

        $noMatchIntent = new Intent($turn, Intent::USER);
        $noMatchIntent->setODId('intent.core.NoMatch');
        $noMatchIntent->setConfidence(1);
        $desiredIntentInterpreted = clone $noMatchIntent;
        $noMatchIntent->addInterpretedIntents(new IntentCollection([$desiredIntentInterpreted]));
        $noMatchIntent->checkForMatch();
        $intents->addObject($noMatchIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->times(5)
            ->andReturn(
                new IntentCollection(),
                new IntentCollection(),
                new IntentCollection(),
                new IntentCollection(),
                new IntentCollection($intents)
            );

        $scenario = new Scenario();
        $scenario->setODId(self::TEST_SCENARIO_1);

        ScenarioSelector::shouldReceive('selectScenarioById')
            ->twice()
            ->andReturn(
                $scenario,
                $scenario
            );

        $conversation = new Conversation();
        $conversation->setODId(self::TEST_CONVERSATION_1);
        ConversationSelector::shouldReceive('selectStartingConversations')
            ->twice()
            ->andReturn(
                new ConversationCollection([$conversation]),
                new ConversationCollection([$conversation])
            );

        $scene = new Scene();
        $scene->setODId(self::TEST_SCENE_1);
        SceneSelector::shouldReceive('selectStartingScenes')
            ->twice()
            ->andReturn(
                new SceneCollection([$scene]),
                new SceneCollection([$scene]),
            );

        $turn = new Turn();
        $turn->setODId(self::TEST_TURN_1);
        TurnSelector::shouldReceive('selectStartingTurns')
            ->twice()
            ->andReturn(
                new TurnCollection([$turn]),
                new TurnCollection([$turn]),
            );

        return $noMatchIntent;
    }

    /**
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingResponse(string $desiredIntentId): Intent
    {
        $scene = $this->createScene();

        $turn = new Turn($scene);
        $turn->setODId(self::TEST_TURN_1);

        TurnSelector::shouldReceive('selectTurnById')
            ->once()
            ->andReturn($turn);

        $intents = new IntentCollection();

        $undesiredIntent = new Intent($turn, Intent::USER);
        $undesiredIntent->setODId('test_undesired_intent');
        $undesiredIntent->setConfidence(0.5);
        $undesiredIntentInterpreted = clone $undesiredIntent;
        $undesiredIntent->addInterpretedIntents(new IntentCollection([$undesiredIntentInterpreted]));
        $undesiredIntent->checkForMatch();
        $intents->addObject($undesiredIntent);

        $desiredIntent = new Intent($turn, Intent::USER);
        $desiredIntent->setODId($desiredIntentId);
        $desiredIntent->setConfidence(0.75);
        $desiredIntentInterpreted = clone $desiredIntent;
        $desiredIntent->addInterpretedIntents(new IntentCollection([$desiredIntentInterpreted]));
        $desiredIntent->checkForMatch();
        $intents->addObject($desiredIntent);

        IntentSelector::shouldReceive('selectResponseIntents')
            ->once()
            ->andReturn($intents);

        return $desiredIntent;
    }

    /**
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForIncomingOngoingValidOriginRequest(string $desiredIntentId): Intent
    {
        $scene = $this->createScene();

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);
        $turn->setValidOrigins([self::TEST_INTENT_1_OUTPUT]);

        TurnSelector::shouldReceive('selectOpenTurns')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        TurnSelector::shouldReceive('selectTurnsByValidOrigin')
            ->once()
            ->andReturn(new TurnCollection([$turn]));

        $intents = new IntentCollection();

        $undesiredIntent = new Intent($turn, Intent::USER);
        $undesiredIntent->setODId('test_undesired_intent');
        $undesiredIntent->setConfidence(0.5);
        $undesiredIntentInterpreted = clone $undesiredIntent;
        $undesiredIntent->addInterpretedIntents(new IntentCollection([$undesiredIntentInterpreted]));
        $undesiredIntent->checkForMatch();
        $intents->addObject($undesiredIntent);

        $desiredIntent = new Intent($turn, Intent::USER);
        $desiredIntent->setODId($desiredIntentId);
        $desiredIntent->setConfidence(0.75);
        $desiredIntentInterpreted = clone $desiredIntent;
        $desiredIntent->addInterpretedIntents(new IntentCollection([$desiredIntentInterpreted]));
        $desiredIntent->checkForMatch();
        $intents->addObject($desiredIntent);

        IntentSelector::shouldReceive('selectRequestIntents')
            ->once()
            ->withArgs(function ($turns) {
                // We only have one turn, but it is both an open turn and one with a matching valid origin
                // so we should check it's not duplicated
                return $turns instanceof TurnCollection && count($turns) === 1;
            })
            ->andReturn($intents);

        return $desiredIntent;
    }

    /**
     * @return Scene
     */
    private function createScene(): Scene
    {
        $scenario = new Scenario();
        $scenario->setODId(self::TEST_SCENARIO_1);

        $conversation = new Conversation($scenario);
        $conversation->setODId(self::TEST_CONVERSATION_1);

        $scene = new Scene($conversation);
        $scene->setODId(self::TEST_SCENE_1);

        return $scene;
    }
}
