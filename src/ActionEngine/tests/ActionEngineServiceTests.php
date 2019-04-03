<?php

namespace OpenDialogAi\ActionEngine\Tests;

use ActionEngine\Exceptions\ActionNotAvailableException;
use ActionEngine\Exceptions\MissingActionRequiredAttributes;
use ActionEngine\Input\ActionInput;
use OpenDialogAi\ActionEngine\Service\ActionEngineService;
use OpenDialogAi\ActionEngine\Tests\Actions\BrokenAction;
use OpenDialogAi\ActionEngine\Tests\Actions\DummyAction;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\BasicAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
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

    public function testPerformActionNotBound()
    {
        try {
            $this->actionEngine->performAction('actions.core.dummy', new ActionInput());
            $this->fail('Exception should have been thrown');
        } catch (ActionNotAvailableException $e) {
            //
        } catch (MissingActionRequiredAttributes $e) {
            $this->fail('Wrong exception thrown');
        }
    }

    public function testPerformActionWithoutRequiredAction()
    {
        $this->setDummyAction();

        try {
            $this->actionEngine->performAction('actions.core.dummy', new ActionInput());
            $this->fail('Exception should have been thrown');
        } catch (ActionNotAvailableException $e) {
            $this->fail('Wrong exception thrown');
        } catch (MissingActionRequiredAttributes $e) {
            //
        }
    }

    public function testPerformActionWithRequiredAction()
    {
        $this->setDummyAction();

        $input = new ActionInput();
        $input->addAttribute(new IntAttribute('attribute.core.dummy', 1));

        try {
            $result = $this->actionEngine->performAction('actions.core.dummy', $input);
            $this->assertTrue($result->isSuccessful());
        } catch (ActionNotAvailableException $e) {
            $this->fail('ActionNotAvailableException should not be thrown');
        } catch (MissingActionRequiredAttributes $e) {
            $this->fail('MissingActionRequiredAttributes should not be thrown');
        }
    }

    public function testGetAttributesFromAction()
    {
        $this->setDummyAction();
        $input = (new ActionInput())->addAttribute(new IntAttribute('attribute.core.dummy', 1));

        $result = $this->actionEngine->performAction('actions.core.dummy', $input);
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('complete', $result->getResultAttribute('attribute.core.dummy2')->getValue());
    }

    protected function setDummyAction(): void
    {
        $this->actionEngine->setAvailableActions([DummyAction::class]);
    }
}
