<?php

namespace OpenDialogAi\ActionEngine\Service;

use OpenDialogAi\ActionEngine\Actions\ActionInput;
use OpenDialogAi\ActionEngine\Actions\ActionResult;

interface ActionEngineInterface
{
    /**
     * Sets the available actions keyed by name of action performed.
     * Uses @param $supportedActions*
     */
    public function setAvailableActions($supportedActions) : void;

    /**
     * Returns a list of all available actions keyed by the action they perform
     *
     * @return array
     */
    public function getAvailableActions() : array;

    /**
     * @param string $actionName The name of the action to perform
     * @param ActionInput $actionInput
     * @return mixed
     * @throw MissingRequiredActionAttributes
     */
    public function performAction(string $actionName) : ActionResult;
}
