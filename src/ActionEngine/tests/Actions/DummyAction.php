<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\Core\Attribute\StringAttribute;

class DummyAction extends BaseAction
{
    protected $requiredAttributes = ['user.name'];

    protected $outputsAttributes = ['user.nickname'];

    protected $performs = 'actions.core.dummy';

    public function perform(ActionInput $actionInput): ActionResult
    {
        $dummyAttribute = new StringAttribute('user.nickname', 'Actionista');
        return ActionResult::createSuccessfulActionResultWithAttributes([$dummyAttribute]);
    }
}
