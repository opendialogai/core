<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Results\ActionResult;

class BrokenAction extends BaseAction
{
    public function perform(): ActionResult
    {
        return new ActionResult();
    }
}
