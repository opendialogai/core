<?php


namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\SessionContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Reasoners\ConditionFilter;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use OpenDialogAi\Core\Tests\TestCase;

class ConditionFilterTest extends TestCase
{
    public function testEmptyCollection()
    {
        $intentCollection = new IntentCollection();

        $this->assertCount(0, ConditionFilter::filterObjects($intentCollection));
    }

    public function testObjectsWithNoConditions()
    {
        $turn1 = new Turn();
        $turn1->setODId('test_turn1');

        $turn2 = new Turn();
        $turn2->setODId('test_turn2');

        $turn3 = new Turn();
        $turn3->setODId('test_turn3');

        $turnCollection = new TurnCollection([
            $turn1,
            $turn2,
            $turn3,
        ]);

        $this->assertCount(3, ConditionFilter::filterObjects($turnCollection));
    }

    public function testObjectsWithConditions()
    {
        ContextService::saveAttribute(SessionContext::getComponentId().".first_name", 'test');

        $expectedScene1 = new Scene();
        $expectedScene1->setODId('test_scene1');
        $expectedScene1->setConditions(new ConditionCollection([
            new Condition('eq', ['attribute' => 'session.first_name'], ['value' => 'test'])
        ]));

        $notExpectedScene = new Scene();
        $notExpectedScene->setODId('test_scene2');
        $notExpectedScene->setConditions(new ConditionCollection([
            new Condition('eq', ['attribute' => 'session.first_name'], ['value' => 'unknown'])
        ]));

        $expectedScene2 = new Scene();
        $expectedScene2->setODId('test_scene3');
        $expectedScene2->setConditions(new ConditionCollection([
            new Condition('eq', ['attribute' => 'session.first_name'], ['value' => 'test'])
        ]));

        $sceneCollection = new SceneCollection([
            $expectedScene1,
            $notExpectedScene,
            $expectedScene2,
        ]);

        $filteredObjects = ConditionFilter::filterObjects($sceneCollection);
        $this->assertCount(2, $filteredObjects);
        $this->assertContains($expectedScene1, $filteredObjects);
        $this->assertContains($expectedScene2, $filteredObjects);
    }

    public function testIntentsWithIntentContextConditions()
    {
        $expectedIntent1 = new Intent();
        $expectedIntent1->setODId('test_intent1');
        $expectedIntent1->setConditions(new ConditionCollection([
            new Condition('eq', ['attribute' => '_intent.first_name'], ['value' => 'test'])
        ]));
        $expectedIntent1->setConfidence(1);
        $expectedIntent1Interpreted = clone $expectedIntent1;
        $expectedIntent1Interpreted->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $expectedIntent1->addInterpretedIntents(new IntentCollection([$expectedIntent1Interpreted]));
        $expectedIntent1->checkForMatch();

        $notExpectedIntent = new Intent();
        $notExpectedIntent->setODId('test_intent2');
        $notExpectedIntent->setConditions(new ConditionCollection([
            new Condition('eq', ['attribute' => '_intent.first_name'], ['value' => 'unknown'])
        ]));
        $notExpectedIntent->setConfidence(1);
        $notExpectedIntentInterpreted = clone $notExpectedIntent;
        $notExpectedIntentInterpreted->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $notExpectedIntent->addInterpretedIntents(new IntentCollection([$notExpectedIntentInterpreted]));
        $notExpectedIntent->checkForMatch();

        $expectedIntent2 = new Intent();
        $expectedIntent2->setODId('test_intent3');
        $expectedIntent2->setConditions(new ConditionCollection([
            new Condition('eq', ['attribute' => '_intent.first_name'], ['value' => 'test'])
        ]));
        $expectedIntent2->setConfidence(1);
        $expectedIntent2Interpreted = clone $expectedIntent2;
        $expectedIntent2Interpreted->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $expectedIntent2->addInterpretedIntents(new IntentCollection([$expectedIntent2Interpreted]));
        $expectedIntent2->checkForMatch();

        $intentCollection = new IntentCollection([
            $expectedIntent1,
            $notExpectedIntent,
            $expectedIntent2,
        ]);

        $filteredIntents = ConditionFilter::filterObjects($intentCollection, true);
        $this->assertCount(2, $filteredIntents);
        $this->assertContains($expectedIntent1, $filteredIntents);
        $this->assertContains($expectedIntent2, $filteredIntents);
    }
}
