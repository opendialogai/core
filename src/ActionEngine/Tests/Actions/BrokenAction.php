<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Actions\BaseAction;

class BrokenAction extends BaseAction
{
    public function perform(ActionInput $actionInput): ActionResult
    {
        return new ActionResult(false);
    }
}
