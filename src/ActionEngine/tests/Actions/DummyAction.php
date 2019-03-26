<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use ActionEngine\Input\ActionInput;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Output\ActionResult;
use OpenDialogAi\Core\Attribute\StringAttribute;

class DummyAction extends BaseAction
{
    protected $requiredAttributes = ['attribute.core.dummy'];

    protected $outputsAttributes = ['attribute.core.dummy2'];

    protected $performs = 'actions.core.dummy';

    public function perform(ActionInput $actionInput): ActionResult
    {
        $dummyAttribute = new StringAttribute('attribute.core.dummy2', 'complete');
        return ActionResult::createSuccessfulActionResultWithAttributes([$dummyAttribute]);
    }
}
