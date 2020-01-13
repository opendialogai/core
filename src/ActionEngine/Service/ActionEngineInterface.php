<?php

namespace OpenDialogAi\ActionEngine\Service;

use Ds\Map;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
use OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException;
use OpenDialogAi\Core\Exceptions\NameNotSetException;

interface ActionEngineInterface
{
    /**
     * Sets the available actions keyed by name of action performed.
     * Uses @param $supportedActions *
     */
    public function setAvailableActions($supportedActions): void;

    /**
     * Helper function to allow you to ignore any predefined config.
     */
    public function unSetAvailableActions(): void;

    /**
     * Returns a list of all available actions keyed by the action they perform
     *
     * @return array
     */
    public function getAvailableActions(): array;

    /**
     * @param string $actionName The name of the action to perform
     * @param Map $inputAttributes
     * @return ActionResult|null
     * @throws ActionNotAvailableException
     */
    public function performAction(string $actionName, Map $inputAttributes): ?ActionResult;

    /**
     * Registers an action to the engine. This method is useful for mocking actions in tests.
     *
     * @param ActionInterface $action
     * @throws NameNotSetException
     */
    public function registerAction(ActionInterface $action): void;
}
