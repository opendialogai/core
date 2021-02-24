<?php


namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\ContextEngine\Contexts\BaseContexts\SessionContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Reasoners\ConditionFilter;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConditionCollection;
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

        $this->assertCount(2, ConditionFilter::filterObjects($sceneCollection));
        $this->assertContains($expectedScene1, ConditionFilter::filterObjects($sceneCollection));
        $this->assertContains($expectedScene2, ConditionFilter::filterObjects($sceneCollection));
    }
}
