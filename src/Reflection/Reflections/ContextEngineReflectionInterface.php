<?php


namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\ContextEngine\ContextManager\ContextInterface;

interface ContextEngineReflectionInterface extends \JsonSerializable
{
    /**
     * @return Map|ContextInterface[]
     */
    public function getAvailableContexts(): Map;
}
