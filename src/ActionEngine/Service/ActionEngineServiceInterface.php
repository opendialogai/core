<?php

namespace OpenDialogAi\ActionEngine\Service;

use ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Results\ActionResult;

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
     * @return mixed
     * @throws ActionNotAvailableException
     */
    public function performAction(string $actionName) : ActionResult;

    /**
     * @param ActionInterface $action The action with attributes to be resolved
     * @return mixed
     */
    public function resolveAttributes(ActionInterface $action);
}
