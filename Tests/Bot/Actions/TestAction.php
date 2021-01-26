<?php

namespace OpenDialogAi\Core\Tests\Bot\Actions;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\AttributeEngine\IntAttribute;

class TestAction extends BaseAction
{
    protected static $name = 'action.test.test';

    protected $outputsAttributes = ['action_test'];

    public function perform(ActionInput $actionInput): ActionResult
    {
        $test = new IntAttribute('action_test', 1);
        return ActionResult::createSuccessfulActionResultWithAttributes([$test]);
    }
}
