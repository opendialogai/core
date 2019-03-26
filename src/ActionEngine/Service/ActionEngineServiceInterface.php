<?php

namespace OpenDialogAi\ActionEngine\Service;

use ActionEngine\Input\ActionInput;
use OpenDialogAi\ActionEngine\Output\ActionResult;

interface ActionEngineServiceInterface
{
    /**
     * Sets the available actions keyed by name of action performed.
     * Uses @see ActionEngineServiceInterface::resolveAttributes() to resolve required attributes
     *
     * @param $supportedActions
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
    public function performAction(string $actionName, ActionInput $actionInput) : ActionResult;
}
