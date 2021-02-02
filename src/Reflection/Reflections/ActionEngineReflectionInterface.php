<?php

namespace OpenDialogAi\Core\Reflection\Reflections;

use Ds\Map;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;

interface ActionEngineReflectionInterface
{
    /**
     * @return Map|ActionInterface[]
     */
    public function getAvailableActions(): Map;
}
