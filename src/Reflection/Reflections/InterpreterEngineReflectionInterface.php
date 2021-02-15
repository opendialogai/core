<?php


namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\InterpreterEngine\InterpreterInterface;

interface InterpreterEngineReflectionInterface extends \JsonSerializable
{
    /**
     * @return Map|InterpreterInterface[]
     */
    public function getAvailableInterpreters(): Map;

    /**
     * @return InterpreterEngineConfiguration
     */
    public function getEngineConfiguration(): InterpreterEngineConfiguration;
}
