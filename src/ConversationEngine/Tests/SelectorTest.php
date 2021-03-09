<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\SessionContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Selectors\ConversationSelector;
use OpenDialogAi\ConversationEngine\Selectors\IntentSelector;
use OpenDialogAi\ConversationEngine\Selectors\ScenarioSelector;
use OpenDialogAi\ConversationEngine\Selectors\SceneSelector;
use OpenDialogAi\ConversationEngine\Selectors\TurnSelector;
use OpenDialogAi\Core\Components\Exceptions\MissingRequiredComponentDataException;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Tests\ConversationGenerator;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\InterpreterEngine\Facades\InterpreterService;


class SelectorTest extends TestCase
{
    /**
     * @var ConditionCollection
     */
    private ConditionCollection $passingConditions;
    /**
     * @var ConditionCollection
     */
    private ConditionCollection $failingConditions;

    /**
     * @throws MissingRequiredComponentDataException
     */
    protected function setUp(): void
    {
        parent::setUp();

        ContextService::saveAttribute(SessionContext::getComponentId().".first_name", 'test');

        $this->passingConditions = new ConditionCollection([
            new Condition('eq', ['attribute' => 'session.first_name'], ['value' => 'test'])
        ]);

        $this->failingConditions = new ConditionCollection([
            new Condition('eq', ['attribute' => 'session.first_name'], ['value' => 'unknown'])
        ]);
    }

    public function testIntentSelectorSelectRequestIntentsNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        IntentSelector::selectRequestIntents(new TurnCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testIntentSelectorSelectRequestIntents()
    {
        $intent1 = ConversationGenerator::createUserIntent('intent_1', $this->failingConditions);
        $intent2 = ConversationGenerator::createUserIntent('intent_2', $this->passingConditions);
        $intent3 = ConversationGenerator::createUserIntent('intent_3', $this->passingConditions);

        $intents = new IntentCollection([$intent1, $intent2, $intent3]);

        $turn = ConversationGenerator::createTurn('turn_1');
        $turn->setRequestIntents($intents);

        ConversationDataClient::shouldReceive('getAllRequestIntents')
            ->once()
            ->andReturn($intents);

        InterpreterService::shouldReceive('interpretDefaultInterpreter')
            ->times(3)
            ->andReturn(
                new IntentCollection([$intent1]),
                new IntentCollection([]),
                new IntentCollection([$intent3])
            );

        /** @var Scenario[]|ScenarioCollection $selectedIntents */
        $selectedIntents = IntentSelector::selectRequestIntents(new TurnCollection([$turn]), true);

        // There were three objects, but one was filtered by interpretation, the other by conditions
        $this->assertCount(1, $selectedIntents);
        $this->assertContains($intent3, $selectedIntents);
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testIntentSelectorIntentContextConditions()
    {
        $passingIntentContextConditions = new ConditionCollection([
            new Condition('eq', ['attribute' => '_intent.first_name'], ['value' => 'test'])
        ]);

        $failingIntentContextConditions = new ConditionCollection([
            new Condition('eq', ['attribute' => '_intent.first_name'], ['value' => 'unknown'])
        ]);

        $intent1 = ConversationGenerator::createUserIntent('intent_1', $failingIntentContextConditions);
        $intent1->setConfidence(1);
        $intent1Interpreted = clone $intent1;
        $intent1Interpreted->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $intent1->addInterpretedIntents(new IntentCollection([$intent1Interpreted]));
        $intent1->checkForMatch();

        $intent2 = ConversationGenerator::createUserIntent('intent_2', $passingIntentContextConditions);
        $intent2->setConfidence(1);
        $intent2Interpreted = clone $intent2;
        $intent2Interpreted->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $intent2->addInterpretedIntents(new IntentCollection([$intent2Interpreted]));
        $intent2->checkForMatch();

        $intent3 = ConversationGenerator::createUserIntent('intent_3', $passingIntentContextConditions);
        $intent3->setConfidence(1);
        $intent3Interpreted = clone $intent3;
        $intent3Interpreted->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $intent3->addInterpretedIntents(new IntentCollection([$intent3Interpreted]));
        $intent3->checkForMatch();

        $intents = new IntentCollection([$intent1, $intent2, $intent3]);

        $turn = ConversationGenerator::createTurn('turn_1');
        $turn->setRequestIntents($intents);

        ConversationDataClient::shouldReceive('getAllRequestIntents')
            ->once()
            ->andReturn($intents);

        InterpreterService::shouldReceive('interpretDefaultInterpreter')
            ->times(3)
            ->andReturn(
                new IntentCollection([$intent1]),
                new IntentCollection([$intent2]),
                new IntentCollection([$intent3])
            );

        /** @var Scenario[]|ScenarioCollection $selectedIntents */
        $selectedIntents = IntentSelector::selectRequestIntents(new TurnCollection([$turn]), true);

        // There were three objects, none were filtered by interpretation, one failing conditions
        $this->assertCount(2, $selectedIntents);
        $this->assertContains($intent2, $selectedIntents);
        $this->assertContains($intent3, $selectedIntents);
    }

    public function testIntentSelectorSelectResponseIntentsNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        IntentSelector::selectResponseIntents(new TurnCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testIntentSelectorSelectResponseIntents()
    {
        $intent1 = ConversationGenerator::createUserIntent('intent_1', $this->failingConditions);
        $intent2 = ConversationGenerator::createUserIntent('intent_2', $this->passingConditions);
        $intent3 = ConversationGenerator::createUserIntent('intent_3', $this->passingConditions);

        $intents = new IntentCollection([$intent1, $intent2, $intent3]);

        $turn = ConversationGenerator::createTurn('turn_1');
        $turn->setResponseIntents($intents);

        ConversationDataClient::shouldReceive('getAllResponseIntents')
            ->once()
            ->andReturn($intents);

        InterpreterService::shouldReceive('interpretDefaultInterpreter')
            ->times(3)
            ->andReturn(
                new IntentCollection([$intent1]),
                new IntentCollection([]),
                new IntentCollection([$intent3])
            );

        /** @var Scenario[]|ScenarioCollection $selectedIntents */
        $selectedIntents = IntentSelector::selectResponseIntents(new TurnCollection([$turn]), true);

        // There were three objects, but one was filtered by interpretation, the other by conditions
        $this->assertCount(1, $selectedIntents);
        $this->assertContains($intent3, $selectedIntents);
    }

    public function testIntentSelectorSelectRequestIntentsByIdNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        IntentSelector::selectRequestIntentsById(new TurnCollection(), 'intent_3');
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testIntentSelectorSelectRequestIntentsById()
    {
        $intent1Passing = ConversationGenerator::createUserIntent('intent_1', $this->passingConditions);
        $intent1Failing = ConversationGenerator::createUserIntent('intent_1', $this->failingConditions);
        $intent1PassingWrongInterpreted = ConversationGenerator::createUserIntent('intent_1', $this->failingConditions);

        $intents = new IntentCollection([$intent1Passing, $intent1Failing, $intent1PassingWrongInterpreted]);

        $turn = ConversationGenerator::createTurn('turn_1');
        $turn->setRequestIntents($intents);

        ConversationDataClient::shouldReceive('getAllRequestIntentsById')
            ->once()
            ->andReturn($intents);

        InterpreterService::shouldReceive('interpretDefaultInterpreter')
            ->times(3)
            ->andReturn(
                new IntentCollection([$intent1Passing]),
                new IntentCollection([$intent1Failing]),
                new IntentCollection([])
            );

        /** @var Scenario[]|ScenarioCollection $selectedIntents */
        $selectedIntents = IntentSelector::selectRequestIntentsById(new TurnCollection([$turn]), 'intent_1', true);

        // There were three objects with the desired ID, but one wasn't interpreted and the other was filtered out by conditions
        $this->assertCount(1, $selectedIntents);
        $this->assertContains($intent1Passing, $selectedIntents);
    }

    public function testIntentSelectorSelectResponseIntentsByIdNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        IntentSelector::selectResponseIntentsById(new TurnCollection(), 'intent_1');
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testIntentSelectorSelectResponseIntentsById()
    {
        $intent1Passing = ConversationGenerator::createUserIntent('intent_1', $this->passingConditions);
        $intent1Failing = ConversationGenerator::createUserIntent('intent_1', $this->failingConditions);
        $intent1PassingWrongInterpreted = ConversationGenerator::createUserIntent('intent_1', $this->failingConditions);

        $intents = new IntentCollection([$intent1Passing, $intent1Failing, $intent1PassingWrongInterpreted]);

        $turn = ConversationGenerator::createTurn('turn_1');
        $turn->setResponseIntents($intents);

        ConversationDataClient::shouldReceive('getAllResponseIntentsById')
            ->once()
            ->andReturn($intents);

        InterpreterService::shouldReceive('interpretDefaultInterpreter')
            ->times(3)
            ->andReturn(
                new IntentCollection([$intent1Passing]),
                new IntentCollection([$intent1Failing]),
                new IntentCollection([])
            );

        /** @var Scenario[]|ScenarioCollection $selectedIntents */
        $selectedIntents = IntentSelector::selectResponseIntentsById(new TurnCollection([$turn]), 'intent_1', true);

        // There were three objects with the desired ID, but one wasn't interpreted and the other was filtered out by conditions
        $this->assertCount(1, $selectedIntents);
        $this->assertContains($intent1Passing, $selectedIntents);
    }

    public function testTurnSelectorSelectStartingTurnsNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        TurnSelector::selectStartingTurns(new SceneCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testTurnSelectorSelectStartingTurns()
    {
        $behaviors = new BehaviorsCollection(new Behavior(Behavior::STARTING_BEHAVIOR));

        $turn1 = ConversationGenerator::createTurn('turn_1', $this->passingConditions);
        $turn1->setBehaviors($behaviors);

        $turn2 = ConversationGenerator::createTurn('turn_2', $this->failingConditions);
        $turn2->setBehaviors($behaviors);

        $turn3 = ConversationGenerator::createTurn('turn_3', $this->passingConditions);
        $turn3->setBehaviors($behaviors);

        $turns = new TurnCollection([$turn1, $turn2, $turn3]);

        $scene = ConversationGenerator::createScene('scene_1');
        $scene->setTurns($turns);

        ConversationDataClient::shouldReceive('getAllStartingTurns')
            ->once()
            ->andReturn($turns);

        /** @var Scenario[]|ScenarioCollection $selectedTurns */
        $selectedTurns = TurnSelector::selectStartingTurns(new SceneCollection([$scene]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedTurns);
        $this->assertContains($turn1, $selectedTurns);
        $this->assertContains($turn3, $selectedTurns);
    }

    public function testTurnSelectorSelectOpenTurnsNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        TurnSelector::selectOpenTurns(new SceneCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testTurnSelectorSelectOpenTurns()
    {
        $behaviors = new BehaviorsCollection(new Behavior(Behavior::OPEN_BEHAVIOR));

        $turn1 = ConversationGenerator::createTurn('turn_1', $this->passingConditions);
        $turn1->setBehaviors($behaviors);

        $turn2 = ConversationGenerator::createTurn('turn_2', $this->failingConditions);
        $turn2->setBehaviors($behaviors);

        $turn3 = ConversationGenerator::createTurn('turn_3', $this->passingConditions);
        $turn3->setBehaviors($behaviors);


        $turns = new TurnCollection([$turn1, $turn2, $turn3]);

        $scene = ConversationGenerator::createScene('scene_1');
        $scene->setTurns($turns);

        ConversationDataClient::shouldReceive('getAllOpenTurns')
            ->once()
            ->andReturn($turns);

        /** @var Scenario[]|ScenarioCollection $selectedTurns */
        $selectedTurns = TurnSelector::selectOpenTurns(new SceneCollection([$scene]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedTurns);
        $this->assertContains($turn1, $selectedTurns);
        $this->assertContains($turn3, $selectedTurns);
    }

    public function testTurnSelectorSelectTurnsNoTurns()
    {
        $this->expectException(EmptyCollectionException::class);
        TurnSelector::selectTurns(new SceneCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testTurnSelectorSelectTurns()
    {

        $turn1 = ConversationGenerator::createTurn('turn_1', $this->passingConditions);
        $turn2 = ConversationGenerator::createTurn('turn_2', $this->failingConditions);
        $turn3 = ConversationGenerator::createTurn('turn_3', $this->passingConditions);

        $turns = new TurnCollection([$turn1, $turn2, $turn3]);

        $scene = ConversationGenerator::createScene('scene_1');
        $scene->setTurns($turns);

        ConversationDataClient::shouldReceive('getAllTurns')
            ->once()
            ->andReturn($turns);

        /** @var Scenario[]|ScenarioCollection $selectedTurns */
        $selectedTurns = TurnSelector::selectTurns(new SceneCollection([$scene]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedTurns);
        $this->assertContains($turn1, $selectedTurns);
        $this->assertContains($turn3, $selectedTurns);
    }

    public function testSceneSelectorSelectStartingScenesNoScenes()
    {
        $this->expectException(EmptyCollectionException::class);
        SceneSelector::selectStartingScenes(new ConversationCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testSceneSelectorSelectStartingScenes()
    {
        $behaviors = new BehaviorsCollection(new Behavior(Behavior::STARTING_BEHAVIOR));

        $scene1 = ConversationGenerator::createScene('scene_1', $this->passingConditions);
        $scene1->setBehaviors($behaviors);

        $scene2 = ConversationGenerator::createScene('scene_2', $this->failingConditions);
        $scene2->setBehaviors($behaviors);

        $scene3 = ConversationGenerator::createScene('scene_3', $this->passingConditions);
        $scene3->setBehaviors($behaviors);

        $scenes = new SceneCollection([$scene1, $scene2, $scene3]);

        $conversation = ConversationGenerator::createConversation('conversation_1');
        $conversation->setScenes($scenes);

        ConversationDataClient::shouldReceive('getAllStartingScenes')
            ->once()
            ->andReturn($scenes);

        /** @var Scenario[]|ScenarioCollection $selectedScenes */
        $selectedScenes = SceneSelector::selectStartingScenes(new ConversationCollection([$conversation]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedScenes);
        $this->assertContains($scene1, $selectedScenes);
        $this->assertContains($scene3, $selectedScenes);
    }

    public function testSceneSelectorSelectOpenScenesNoScenes()
    {
        $this->expectException(EmptyCollectionException::class);
        SceneSelector::selectOpenScenes(new ConversationCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testSceneSelectorSelectOpenScenes()
    {
        $behaviors = new BehaviorsCollection(new Behavior(Behavior::OPEN_BEHAVIOR));

        $scene1 = ConversationGenerator::createScene('scene_1', $this->passingConditions);
        $scene1->setBehaviors($behaviors);

        $scene2 = ConversationGenerator::createScene('scene_2', $this->failingConditions);
        $scene2->setBehaviors($behaviors);

        $scene3 = ConversationGenerator::createScene('scene_3', $this->passingConditions);
        $scene3->setBehaviors($behaviors);

        $scenes = new SceneCollection([$scene1, $scene2, $scene3]);

        $conversation = ConversationGenerator::createConversation('conversation_1');
        $conversation->setScenes($scenes);

        ConversationDataClient::shouldReceive('getAllOpenScenes')
            ->once()
            ->andReturn($scenes);

        /** @var Scenario[]|ScenarioCollection $selectedScenes */
        $selectedScenes = SceneSelector::selectOpenScenes(new ConversationCollection([$conversation]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedScenes);
        $this->assertContains($scene1, $selectedScenes);
        $this->assertContains($scene3, $selectedScenes);
    }

    public function testSceneSelectorSelectScenesNoScenes()
    {
        $this->expectException(EmptyCollectionException::class);
        SceneSelector::selectScenes(new ConversationCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testSceneSelectorSelectScenes()
    {
        $scene1 = ConversationGenerator::createScene('scene_1', $this->passingConditions);
        $scene2 = ConversationGenerator::createScene('scene_2', $this->failingConditions);
        $scene3 = ConversationGenerator::createScene('scene_3', $this->passingConditions);

        $scenes = new SceneCollection([$scene1, $scene2, $scene3]);

        $conversation = ConversationGenerator::createConversation('conversation_1');
        $conversation->setScenes($scenes);

        ConversationDataClient::shouldReceive('getAllScenes')
            ->once()
            ->andReturn($scenes);

        /** @var Scenario[]|ScenarioCollection $selectedScenes */
        $selectedScenes = SceneSelector::selectScenes(new ConversationCollection([$conversation]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedScenes);
        $this->assertContains($scene1, $selectedScenes);
        $this->assertContains($scene3, $selectedScenes);
    }

    public function testConversationSelectorSelectStartingConversationsNoConversations()
    {
        $this->expectException(EmptyCollectionException::class);
        ConversationSelector::selectStartingConversations(new ScenarioCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testConversationSelectorSelectStartingConversations()
    {
        $behaviors = new BehaviorsCollection(new Behavior(Behavior::STARTING_BEHAVIOR));

        $conversation1 = ConversationGenerator::createConversation('conversation_1', $this->passingConditions);
        $conversation1->setBehaviors($behaviors);

        $conversation2 = ConversationGenerator::createConversation('conversation_2', $this->failingConditions);
        $conversation2->setBehaviors($behaviors);

        $conversation3 = ConversationGenerator::createConversation('conversation_3', $this->passingConditions);
        $conversation3->setBehaviors($behaviors);

        $conversations = new ConversationCollection([$conversation1, $conversation2, $conversation3]);

        $scenario = ConversationGenerator::createScenario('scenario_1');
        $scenario->setConversations($conversations);

        ConversationDataClient::shouldReceive('getAllStartingConversations')
            ->once()
            ->andReturn($conversations);

        /** @var Scenario[]|ScenarioCollection $selectedConversations */
        $selectedConversations = ConversationSelector::selectStartingConversations(new ScenarioCollection([$scenario]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedConversations);
        $this->assertContains($conversation1, $selectedConversations);
        $this->assertContains($conversation3, $selectedConversations);
    }

    public function testConversationSelectorSelectOpenConversationsNoConversations()
    {
        $this->expectException(EmptyCollectionException::class);
        ConversationSelector::selectOpenConversations(new ScenarioCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testConversationSelectorSelectOpenConversations()
    {
        $behaviors = new BehaviorsCollection(new Behavior(Behavior::OPEN_BEHAVIOR));

        $conversation1 = ConversationGenerator::createConversation('conversation_1', $this->passingConditions);
        $conversation1->setBehaviors($behaviors);

        $conversation2 = ConversationGenerator::createConversation('conversation_2', $this->failingConditions);
        $conversation2->setBehaviors($behaviors);

        $conversation3 = ConversationGenerator::createConversation('conversation_3', $this->passingConditions);
        $conversation3->setBehaviors($behaviors);

        $conversations = new ConversationCollection([$conversation1, $conversation2, $conversation3]);

        $scenario = ConversationGenerator::createScenario('scenario_1');
        $scenario->setConversations($conversations);

        ConversationDataClient::shouldReceive('getAllOpenConversations')
            ->once()
            ->andReturn($conversations);

        /** @var Scenario[]|ScenarioCollection $selectedConversations */
        $selectedConversations = ConversationSelector::selectOpenConversations(new ScenarioCollection([$scenario]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedConversations);
        $this->assertContains($conversation1, $selectedConversations);
        $this->assertContains($conversation3, $selectedConversations);
    }

    public function testConversationSelectorSelectConversationsNoConversations()
    {
        $this->expectException(EmptyCollectionException::class);
        ConversationSelector::selectConversations(new ScenarioCollection());
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testConversationSelectorSelectConversations()
    {
        $conversation1 = ConversationGenerator::createConversation('conversation_1', $this->passingConditions);
        $conversation2 = ConversationGenerator::createConversation('conversation_2', $this->failingConditions);
        $conversation3 = ConversationGenerator::createConversation('conversation_3', $this->passingConditions);

        $conversations = new ConversationCollection([$conversation1, $conversation2, $conversation3]);

        $scenario = ConversationGenerator::createScenario('scenario_1');
        $scenario->setConversations($conversations);

        ConversationDataClient::shouldReceive('getAllConversations')
            ->once()
            ->andReturn($conversations);

        /** @var Scenario[]|ScenarioCollection $selectedConversations */
        $selectedConversations = ConversationSelector::selectConversations(new ScenarioCollection([$scenario]));

        // There were three objects but one was filtered out by conditions
        $this->assertCount(2, $selectedConversations);
        $this->assertContains($conversation1, $selectedConversations);
        $this->assertContains($conversation3, $selectedConversations);
    }

    public function testScenarioSelectorSelectScenarios()
    {
        $scenario1 = ConversationGenerator::createScenario('scenario_1', $this->passingConditions);
        $scenario2 = ConversationGenerator::createScenario('scenario_2', $this->failingConditions);
        $scenario3 = ConversationGenerator::createScenario('scenario_3', $this->passingConditions);

        $scenarios = new ScenarioCollection([$scenario1, $scenario2, $scenario3]);

        ConversationDataClient::shouldReceive('getAllActiveScenarios')
            ->once()
            ->andReturn($scenarios);

        /** @var Scenario[]|ScenarioCollection $selectedScenarios */
        $selectedScenarios = ScenarioSelector::selectScenarios();

        // There were three objects, but one was filtered out by conditions
        $this->assertCount(2, $selectedScenarios);
        $this->assertContains($scenario1, $selectedScenarios);
        $this->assertContains($scenario3, $selectedScenarios);
    }
}
