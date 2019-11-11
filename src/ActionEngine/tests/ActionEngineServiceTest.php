<?php

namespace OpenDialogAi\ActionEngine\Tests;

use Ds\Map;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Service\ActionEngine;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ActionEngine\Tests\Actions\BrokenAction;
use OpenDialogAi\ActionEngine\Tests\Actions\DummyAction;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ActionEngineServiceTest extends TestCase
{
    /** @var ActionEngine */
    private $actionEngine;

    public function setUp(): void
    {
        parent::setUp();
        $this->actionEngine = $this->app->make(ActionEngineInterface::class);
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
        $this->actionEngine->performAction('actions.core.dummy', new Map());
    }

    public function testPerformActionWithoutRequiredAction()
    {
        $this->setDummyAction();
        ContextService::createContext('test');

        $inputAttributes = new Map([
            'name' => 'test',
        ]);

        try {
            $result = $this->actionEngine->performAction('actions.core.dummy', $inputAttributes);
            $this->assertTrue($result->isSuccessful());
        } catch (ActionNotAvailableException $e) {
            $this->fail('Wrong exception thrown');
        }
    }

    public function testPerformActionWithRequiredAction()
    {
        $this->setDummyAction();
        ContextService::createContext('test');

        $inputAttributes = new Map([
            'name' => 'test',
        ]);

        try {
            $result = $this->actionEngine->performAction('actions.core.dummy', $inputAttributes);
            $this->assertTrue($result->isSuccessful());
        } catch (ActionNotAvailableException $e) {
            $this->fail('ActionNotAvailableException should not be thrown');
        }
    }

    public function testPerformActionWithRequiredAttributes()
    {
        try {
            $result = $this->actionEngine->performAction('action.core.example', new Map());
            $this->assertTrue($result === null);

            $inputAttributes = new Map([
                'first_name' => 'user',
                'last_name' => 'user',
            ]);

            $result = $this->actionEngine->performAction('action.core.example', $inputAttributes);
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
        ContextService::createContext('test');
        $testAttribute = new StringAttribute('name', 'John');
        ContextService::getContext('test')->addAttribute($testAttribute);

        $inputAttributes = new Map([
            'name' => 'test',
        ]);

        $result = $this->actionEngine->performAction('actions.core.dummy', $inputAttributes);
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
