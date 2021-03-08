<?php

namespace OpenDialogAi\OperationEngine\Service;

use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\OperationEngine\OperationInterface;

interface OperationServiceInterface
{
    /**
     * Returns a list of all available operations keyed by name
     *
     * @return OperationInterface[]
     */
    public function getAvailableOperations() : array;

    /**
     * Checks if an operation with the given name has been registered
     *
     * @param string $operationName
     * @return bool
     */
    public function isOperationAvailable(string $operationName) : bool;

    /**
     * Gets the registered operation by name if it is registered
     *
     * @param $operationName
     * @return OperationInterface
     */
    public function getOperation($operationName) : OperationInterface;

    /**
     * @param $operations
     */
    public function registerAvailableOperations($operations): void;

    /**
     * @param Condition $condition
     * @return bool
     */
    public function checkCondition(Condition $condition): bool;
}
