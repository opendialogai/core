<?php

namespace OpenDialogAi\ActionEngine\Tests\Actions;

use OpenDialogAi\ActionEngine\Actions\BaseAction;
use OpenDialogAi\ActionEngine\Results\ActionResult;

class DummyAction extends BaseAction
{
    protected $requires = ['attribute.dummy'];

    protected $performs = 'actions.core.dummy';

    public function perform(): ActionResult
    {
        return new ActionResult();
    }
}
