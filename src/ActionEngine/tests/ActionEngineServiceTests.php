<?php

namespace OpenDialogAi\ActionEngine\Tests;

use ActionEngine\Exceptions\ActionNotAvailableException;
use ActionEngine\Exceptions\AttributeNotResolvedException;
use OpenDialogAi\ActionEngine\Service\ActionEngineService;
use OpenDialogAi\ActionEngine\Tests\Actions\BrokenAction;
use OpenDialogAi\ActionEngine\Tests\Actions\DummyAction;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\BasicAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ActionEngineServiceTests extends TestCase
{
    /** @var ActionEngineService */
    private $actionEngine;

    /** @var AttributeInterface */
    private $anythingAttribute;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->anythingAttribute = new BasicAttribute('anything', AbstractAttribute::STRING, 'anything');

        $actionEngineService = new ActionEngineService();

        $this->mock(AttributeResolverService::class, function ($mock) {
            $mock->shouldReceive('getAttributeFor')->andReturn(
                $this->anythingAttribute
            );
        });

        $actionEngineService->setAttributeResolver(app()->make(AttributeResolverService::class));

        $this->actionEngine = $actionEngineService;
    }

    public function testSettingNonExistentAction()
    {
        $actions = ['DoesNotExist.php'];

        $this->actionEngine->setAvailableActions($actions);

        $this->assertCount(0, $this->actionEngine->getAvailableActions());
    }

    public function testSettingActionWithNoName()
    {
        $actions = [BrokenAction::class];

        $this->actionEngine->setAvailableActions($actions);

        $this->assertCount(0, $this->actionEngine->getAvailableActions());
    }

    public function testSettingValidAction()
    {
        $this->actionEngine->setAvailableActions([DummyAction::class]);

        $this->assertCount(1, $this->actionEngine->getAvailableActions());

        $availableActions = $this->actionEngine->getAvailableActions();

        $this->assertEquals('actions.core.dummy', array_shift($availableActions)->performs());
    }

    public function testCombination()
    {
        $this->actionEngine->setAvailableActions([DummyAction::class, 'DoesNotExist.php', BrokenAction::class]);

        $this->assertCount(1, $this->actionEngine->getAvailableActions());

        $availableActions = $this->actionEngine->getAvailableActions();

        $this->assertEquals('actions.core.dummy', array_shift($availableActions)->performs());
    }

    public function testAttributeResolving()
    {
        $action = new DummyAction();

        try {
            $this->assertNull($action->getAttribute('attribute.dummy'));
            $this->fail("Should have thrown an exception");
        } catch (AttributeNotResolvedException $e) {
            //
        }

        $this->actionEngine->resolveAttributes($action);

        try {
            $this->assertEquals($this->anythingAttribute, $action->getAttribute('attribute.dummy'));
        } catch (AttributeNotResolvedException $e) {
            $this->fail('Exception should not have been thrown');
        }
    }

    public function testPerformActionNotBound()
    {
        try {
            $this->actionEngine->performAction('actions.core.dummy');
            $this->fail('Exception should have been thrown');
        } catch (ActionNotAvailableException $e) {
            //
        }
    }

    public function testPerformAction()
    {
        $this->actionEngine->setAvailableActions([DummyAction::class]);

        try {
            $result = $this->actionEngine->performAction('actions.core.dummy');
            $this->assertTrue($result->isSuccessful());
        } catch (ActionNotAvailableException $e) {
            $this->fail('Exception should not have been thrown');
        }
    }
}
