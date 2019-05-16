<?php

namespace OpenDialogAi\ActionEngine\Tests;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Service\ActionEngine;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ActionEngine\Tests\Actions\BrokenAction;
use OpenDialogAi\ActionEngine\Tests\Actions\DummyAction;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ActionEngineServiceTest extends TestCase
{
    /** @var ActionEngine */
    private $actionEngine;

    /** @var ContextService */
    private $contextService;

    public function setUp(): void
    {
        parent::setUp();
        $this->actionEngine = $this->app->make(ActionEngineInterface::class);
        $this->contextService = $this->app->make(ContextService::class);
    }

    public function testSettingNonExistentAction()
    {
        $this->actionEngine->unsetAvailableActions();
        $actions = ['DoesNotExist.php'];

        $this->actionEngine->setAvailableActions($actions);

        $this->assertCount(0, $this->actionEngine->getAvailableActions());
    }

    public function testSettingActionWithNoName()
    {
        $this->actionEngine->unsetAvailableActions();
        $actions = [BrokenAction::class];

        $this->actionEngine->setAvailableActions($actions);

        $this->assertCount(0, $this->actionEngine->getAvailableActions());
    }

    public function testSettingValidAction()
    {
        $this->actionEngine->unsetAvailableActions();
        $this->setDummyAction();

        $this->assertCount(1, $this->actionEngine->getAvailableActions());

        $availableActions = $this->actionEngine->getAvailableActions();

        $this->assertEquals('actions.core.dummy', array_shift($availableActions)->performs());
    }

    public function testCombination()
    {
        $this->actionEngine->unsetAvailableActions();
        $this->actionEngine->setAvailableActions([DummyAction::class, 'DoesNotExist.php', BrokenAction::class]);

        $this->assertCount(1, $this->actionEngine->getAvailableActions());

        $availableActions = $this->actionEngine->getAvailableActions();

        $this->assertEquals('actions.core.dummy', array_shift($availableActions)->performs());
    }

    /**
     * @throws ActionNotAvailableException
     */
    public function testPerformActionNotBound()
    {
        $this->expectException(ActionNotAvailableException::class);
        $this->actionEngine->performAction('actions.core.dummy');
    }

    public function testPerformActionWithoutRequiredAction()
    {
        $this->setDummyAction();
        $this->contextService->createContext('test');


        $this->expectException(AttributeDoesNotExistException::class);
        try {
            $this->actionEngine->performAction('actions.core.dummy');
            $this->fail('Exception should have been thrown');
        } catch (ActionNotAvailableException $e) {
            $this->fail('Wrong exception thrown');
        }
    }

    public function testPerformActionWithRequiredAction()
    {
        $this->setDummyAction();
        $this->contextService->createContext('test');

        $input = new ActionInput();
        $input->addAttribute(new IntAttribute('dummy', 1));

        $this->expectException(AttributeDoesNotExistException::class);
        try {
            $result = $this->actionEngine->performAction('actions.core.dummy');
            $this->assertTrue($result->isSuccessful());
        } catch (ActionNotAvailableException $e) {
            $this->fail('ActionNotAvailableException should not be thrown');
        }
    }

    /**
     * @throws ActionNotAvailableException
     */
    public function testGetAttributesFromAction()
    {
        $this->setDummyAction();
        $this->contextService->createContext('test');
        $testAttribute = new StringAttribute('name', 'John');
        $this->contextService->getContext('test')->addAttribute($testAttribute);

        $result = $this->actionEngine->performAction('actions.core.dummy');
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Actionista', $result->getResultAttribute('nickname')->getValue());
    }

    public function testCustomActions()
    {
        $this->setConfigValue('opendialog.action_engine.custom_actions', [DummyAction::class]);

        /** @var ActionEngineInterface $actionEngine */
        $actionEngine = app()->make(ActionEngineInterface::class);

        $this->assertContains('actions.core.dummy', array_keys($actionEngine->getAvailableActions()));
    }

    protected function setDummyAction(): void
    {
        $this->actionEngine->setAvailableActions([DummyAction::class]);
    }
}
