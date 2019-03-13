<?php

namespace OpenDialogAi\ActionEngine\Actions;

use OpenDialogAi\ActionEngine\Results\ActionResult;

/**
 * This is a placeholder interface for what an action needs to de
 */
interface ActionInterface
{
    public function performActions() : array;

    public function perform(string $action) : ActionResult;
}
