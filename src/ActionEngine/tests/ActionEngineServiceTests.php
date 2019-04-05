<?php

namespace OpenDialogAi\ActionEngine\Tests;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Exceptions\MissingActionRequiredAttributes;
use OpenDialogAi\ActionEngine\Service\ActionEngine;
use OpenDialogAi\ActionEngine\Tests\Actions\BrokenAction;
use OpenDialogAi\ActionEngine\Tests\Actions\DummyAction;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ActionEngineServiceTests extends TestCase
{
    /** @var ActionEngine */
    private $actionEngine;

    public function setUp(): void
    {
        parent::setUp();
        $this->actionEngine = $this->app->make(ActionEngine::class);
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
        $this->setDummyAction();

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

    /**
     * @throws ActionNotAvailableException
     * @throws MissingActionRequiredAttributes
     */
    public function testPerformActionNotBound()
    {
        $this->expectException(ActionNotAvailableException::class);
        $this->actionEngine->performAction('actions.core.dummy', new ActionInput());
    }

    /**
     * @throws MissingActionRequiredAttributes
     */
    public function testPerformActionWithoutRequiredAction()
    {
        $this->setDummyAction();

        $this->expectException(MissingActionRequiredAttributes::class);
        try {
            $this->actionEngine->performAction('actions.core.dummy', new ActionInput());
            $this->fail('Exception should have been thrown');
        } catch (ActionNotAvailableException $e) {
            $this->fail('Wrong exception thrown');
        }
    }

    /**
     * @throws MissingActionRequiredAttributes
     */
    public function testPerformActionWithRequiredAction()
    {
        $this->setDummyAction();

        $input = new ActionInput();
        $input->addAttribute(new IntAttribute('dummy', 1));

        $this->expectException(MissingActionRequiredAttributes::class);
        try {
            $result = $this->actionEngine->performAction('actions.core.dummy', $input);
            $this->assertTrue($result->isSuccessful());
        } catch (ActionNotAvailableException $e) {
            $this->fail('ActionNotAvailableException should not be thrown');
        }
    }

    /**
     * @throws ActionNotAvailableException
     * @throws MissingActionRequiredAttributes
     */
    public function testGetAttributesFromAction()
    {
        $this->setDummyAction();
        $input = (new ActionInput())->addAttribute(new IntAttribute('name', 'John'));

        $result = $this->actionEngine->performAction('actions.core.dummy', $input);
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Actionista', $result->getResultAttribute('nickname')->getValue());
    }

    protected function setDummyAction(): void
    {
        $this->actionEngine->setAvailableActions([DummyAction::class]);
    }
}
