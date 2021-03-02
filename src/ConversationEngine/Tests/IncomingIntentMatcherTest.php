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
    const TEST_INTENT_1 = 'test_intent1';
    const TEST_INTENT_1_RESPONSE = 'test_intent1_response';
    const TEST_TURN_2 = 'test_turn2';
    const TEST_INTENT_2 = 'test_intent2';

    public function testNoMatchingIntents()
    {
        // Mock selectors, no request intents will be selected
        $intents = new IntentCollection();
        $this->mockSelectorsForStarting($intents);

        // Set conversational state
        $this->updateStateToUndefined();

        $this->expectException(NoMatchingIntentsException::class);
        IncomingIntentMatcher::matchIncomingIntent();
    }

    public function testBasicAsRequestMatch()
    {
        // Mock selectors, a request intent will be selected
        $intent = new Intent();
        $intent->setODId(self::TEST_INTENT_1);
        $intents = new IntentCollection([$intent]);
        $this->mockSelectorsForStarting($intents);

        // Set conversational state
        $this->updateStateToUndefined();

        $this->assertSame($intent, IncomingIntentMatcher::matchIncomingIntent());
    }

    public function testOngoingAsRequestMatchWithOpenTurns()
    {
        // Mock selectors, a request intent will be selected
        $desiredIntent = $this->mockSelectorsForOngoingOpenTurns(self::TEST_INTENT_2);

        // Set conversational state
        $this->updateStateToOngoing();

        $this->assertSame($desiredIntent, IncomingIntentMatcher::matchIncomingIntent());
    }

    public function testOngoingAsRequestMatchWithValidOrigin()
    {
        // Mock selectors, a request intent will be selected
        $desiredIntent = $this->mockSelectorsForOngoingValidOrigins(self::TEST_INTENT_2);

        // Set conversational state
        $this->updateStateToOngoing();

        $this->assertSame($desiredIntent, IncomingIntentMatcher::matchIncomingIntent());
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

    private function updateStateToOngoing()
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
            self::TEST_INTENT_1_RESPONSE
        );
        ContextService::saveAttribute(
            $conversationContextId .'.'.Intent::CURRENT_SPEAKER,
            Intent::APP
        );
    }

    /**
     * @param IntentCollection $intents
     */
    private function mockSelectorsForStarting(IntentCollection $intents): void
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
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForOngoingOpenTurns(string $desiredIntentId): Intent
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
     * @param string $desiredIntentId
     * @return Intent
     */
    private function mockSelectorsForOngoingValidOrigins(string $desiredIntentId): Intent
    {
        $scene = $this->mockSelectorsForOngoing();

        $turn = new Turn($scene);
        $turn->setBehaviors(new BehaviorsCollection([new Behavior(Behavior::OPEN_BEHAVIOR)]));
        $turn->setODId(self::TEST_TURN_2);
        $turn->setValidOrigins([self::TEST_INTENT_1_RESPONSE]);

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
