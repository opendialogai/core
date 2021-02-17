<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;

class DummyAction extends BaseAction
{
    protected static array $requiredAttributes = ['name'];

    protected static array $outputAttributes = ['nickname'];

    protected static string $componentId = 'actions.core.dummy';

    public function perform(ActionInput $actionInput): ActionResult
    {
        $dummyAttribute = new StringAttribute('nickname', 'Actionista');
        return ActionResult::createSuccessfulActionResultWithAttributes([$dummyAttribute]);
    }
}
