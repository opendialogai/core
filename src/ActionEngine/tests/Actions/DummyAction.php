<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Results\ActionResult;

class DummyAction extends BaseAction
{
    protected $requires = ['action.dummy'];

    protected $performs = 'actions.core.dummy';

    public function perform(string $action): ActionResult
    {
        return new ActionResult();
    }
}
