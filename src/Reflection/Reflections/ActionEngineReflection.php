<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\ActionEngine\Actions\ActionInterface;
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
        $actions = $this->actionEngine->getAvailableActions();

        $actionsWithData = array_map(function ($action) {
            /** @var ActionInterface $action */
            return [
                'component_data' => (array) $action::getComponentData(),
            ];
        }, $actions);

        return new Map($actionsWithData);
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
