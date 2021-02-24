<?php

namespace OpenDialogAi\ActionEngine\Service;

use Illuminate\Support\Collection;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
use OpenDialogAi\ActionEngine\Actions\ActionResult;
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
     * @param Collection $inputAttributes
     * @return ActionResult|null
     */
    public function performAction(string $actionName, Collection $inputAttributes): ?ActionResult;

    /**
     * Registers an action to the engine. This method is useful for mocking actions in tests.
     *
     * @param ActionInterface $action
     * @throws NameNotSetException
     */
    public function registerAction(ActionInterface $action): void;
}
