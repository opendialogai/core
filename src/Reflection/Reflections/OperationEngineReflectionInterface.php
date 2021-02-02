<?php


namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\OperationEngine\OperationInterface;

interface OperationEngineReflectionInterface
{
    /**
     * @return Map|OperationInterface[]
     */
    public function getAvailableOperations(): Map;
}
