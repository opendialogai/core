<?php

namespace OpenDialogAi\ActionEngine\Actions;

use OpenDialogAi\ActionEngine\Results\ActionResult;

class DummyAction extends BaseAction
{
    protected $performs = ['action.dummy'];

    public function perform(string $action): ActionResult
    {
        return new ActionResult();
    }
}
