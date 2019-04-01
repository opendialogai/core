<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use ActionEngine\Input\ActionInput;
use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Output\ActionResult;

class BrokenAction extends BaseAction
{
    public function perform(ActionInput $actionInput): ActionResult
    {
        return new ActionResult(false);
    }
}
