<?php

namespace OpenDialogAi\ActionEngine\Tests;

use OpenDialogAi\ActionEngine\Service\ActionEngineService;
use OpenDialogAi\ActionEngine\Tests\Actions\BrokenAction;
use OpenDialogAi\ActionEngine\Tests\Actions\DummyAction;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\Core\Tests\TestCase;

class ActionEngineServiceTests extends TestCase
{
    /** @var ActionEngineService */
    private $actionEngine;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $actionEngineService = new ActionEngineService();
        $actionEngineService->setAttributeResolver(app()->make(AttributeResolverService::ATTRIBUTE_RESOLVER));

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
}
