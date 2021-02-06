<?php


namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\OperationEngine\OperationInterface;

interface OperationEngineReflectionInterface extends \JsonSerializable
{
    /**
     * @return Map|OperationInterface[]
     */
    public function getAvailableOperations(): Map;
}
