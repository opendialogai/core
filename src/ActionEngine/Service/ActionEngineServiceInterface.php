<?php

namespace OpenDialogAi\ActionEngine\Service;

use OpenDialogAi\ActionEngine\Actions\ActionInterface;

interface ActionEngineServiceInterface
{
    /**
     * Sets the available actions keyed by name of action performed.
     *
     * @param $supportedActions
     */
    public function setAvailableActions($supportedActions) : void;

    public function getAvailableActions();

    public function performAction(string $actionName);

    public function resolveAttributes(ActionInterface $action);
}
