<?php

namespace OpenDialogAi\ConversationEngine\Tests;


use Mockery;
use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Facades\ActionService;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\SessionContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Contexts\User\UserDataClient;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\Reasoners\ActionPerformer;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\ActionsCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;

class ActionPerformerTest extends TestCase
{
    public function testActionPerformerWithEmptyAction()
    {
        $action = new Action('action.fake.unknown');

        ActionPerformer::performAction($action);

        // Assert that the performer silently skipped the fake action
        $this->assertTrue(true);
    }

    public function testActionPerformerWithAction()
    {
        $this->registerUppercaseFirstNameAction();

        ContextService::saveAttribute(SessionContext::getComponentId().'.first_name', 'my_name');

        $action = new Action('action.test.first_name_uppercase', collect([
            'first_name' => 'session'
        ]), collect([
            'first_name' => 'session'
        ]));

        ActionPerformer::performAction($action);

        $this->assertEquals(
            'MY_NAME',
            ContextService::getAttributeValue('first_name', SessionContext::getComponentId())
        );
    }

    /**
     * This test is currently skipped pending further work on composite attributes
     * @group skip
     */
    public function testActionPerformerWithActionAndCompositeAttributes()
    {
        $this->registerUppercaseFirstNameAction();

        ContextService::saveAttribute(SessionContext::getComponentId().'.composite[first_name]', 'my_name');

        $action = new Action('action.test.first_name_uppercase', collect([
            'composite[first_name]' => 'session'
        ]), collect([
            'composite[first_name]' => 'session'
        ]));

        ActionPerformer::performAction($action);

        $this->assertEquals(
            'MY_NAME',
            ContextService::getAttributeValue('composite[first_name]', SessionContext::getComponentId())
        );
    }

    public function testActionPerformerWithMultipleDependentActions()
    {
        ActionService::registerAction(new class extends BaseAction {
            protected static string $componentId = 'action.test.plus_five';

            protected static array $inputAttributes = ['age'];
            protected static array $outputAttributes = ['age'];

            /**
             * @inheritDoc
             */
            public function perform(ActionInput $actionInput): ActionResult
            {
                $age = $actionInput->getAttributeBag()->getAttributeValue('age');

                return ActionResult::createSuccessfulActionResultWithAttributes([
                    AttributeResolver::getAttributeFor('age', $age + 5)
                ]);
            }
        });

        ActionService::registerAction(new class extends BaseAction {
            protected static string $componentId = 'action.test.times_three';

            protected static array $inputAttributes = ['age'];
            protected static array $outputAttributes = ['age'];

            /**
             * @inheritDoc
             */
            public function perform(ActionInput $actionInput): ActionResult
            {
                $age = $actionInput->getAttributeBag()->getAttributeValue('age');

                return ActionResult::createSuccessfulActionResultWithAttributes([
                    AttributeResolver::getAttributeFor('age', $age * 3)
                ]);
            }
        });

        $initialNumber = 25;
        ContextService::saveAttribute(SessionContext::getComponentId().'.age', $initialNumber);

        $plusFiveAction = new Action('action.test.plus_five', collect([
            'age' => 'session'
        ]), collect([
            'age' => 'session'
        ]));

        $timesThreeAction = new Action('action.test.times_three', collect([
            'age' => 'session'
        ]), collect([
            'age' => 'session'
        ]));

        ActionPerformer::performActions(new ActionsCollection([
            $plusFiveAction,
            $timesThreeAction,
            $plusFiveAction,
        ]));

        $this->assertEquals(
            (($initialNumber + 5) * 3) + 5,
            ContextService::getAttributeValue('age', SessionContext::getComponentId())
        );
    }


    public function testActionPerformerWithIntentAndPersistentContext()
    {
        $mockedUserContext = Mockery::mock(UserContext::class."[persist]", [new UserDataClient()]);
        $mockedUserContext
            ->shouldReceive('persist')->once()
            ->andReturnTrue();

        $this->app->singleton(UserContext::class, fn() => $mockedUserContext);

        $this->registerUppercaseFirstNameAction();

        ContextService::saveAttribute(UserContext::getComponentId().'.first_name', 'my_name');

        $action = new Action('action.test.first_name_uppercase', collect([
            'first_name' => UserContext::getComponentId()
        ]), collect([
            'first_name' => UserContext::getComponentId()
        ]));

        $intent = new Intent();
        $intent->setActions(new ActionsCollection([
            $action
        ]));

        ActionPerformer::performActionsForIntent($intent);

        $this->assertEquals(
            'MY_NAME',
            ContextService::getAttributeValue('first_name', UserContext::getComponentId())
        );
    }

    private function registerUppercaseFirstNameAction(): void
    {
        ActionService::registerAction(new class extends BaseAction {
            protected static string $componentId = 'action.test.first_name_uppercase';

            protected static array $inputAttributes = ['first_name'];
            protected static array $outputAttributes = ['first_name'];

            /**
             * @inheritDoc
             */
            public function perform(ActionInput $actionInput): ActionResult
            {
                $firstName = $actionInput->getAttributeBag()->getAttributeValue('first_name');

                return ActionResult::createSuccessfulActionResultWithAttributes([
                    AttributeResolver::getAttributeFor('first_name', strtoupper($firstName))
                ]);
            }
        });
    }
}
