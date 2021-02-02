<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;

class ActionEngineReflection implements ActionEngineReflectionInterface
{
    /**
     * @var ActionEngineInterface
     */
    private $actionEngine;

    /**
     * ActionReflectionComponents constructor.
     * @param ActionEngineInterface $actionEngine
     */
    public function __construct(ActionEngineInterface $actionEngine)
    {
        $this->actionEngine = $actionEngine;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableActions(): Map
    {
        return new Map($this->actionEngine->getAvailableActions());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "available_actions" => $this->getAvailableActions()->toArray(),
        ];
    }
}
