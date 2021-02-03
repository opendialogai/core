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
        return new Map($this->actionEngine->getAvailableActions());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $actions = $this->getAvailableActions();

        $actionsWithData = array_map(function ($action) {
            /** @var ActionInterface $action */
            return [
                'component_data' => (array) $action::getComponentData(),
                'action_data' => [
                    'required_attributes' => $action::getRequiredAttributes(),
                    'output_attributes' => $action::getOutputAttributes(),
                ]
            ];
        }, $actions->toArray());

        return [
            "available_actions" => $actionsWithData,
        ];
    }
}
