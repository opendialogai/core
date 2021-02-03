<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
use OpenDialogAi\OperationEngine\OperationInterface;
use OpenDialogAi\OperationEngine\Service\OperationServiceInterface;

class OperationEngineReflection implements OperationEngineReflectionInterface
{
    /** @var OperationServiceInterface */
    private $operationService;

    /**
     * OperationEngineReflection constructor.
     * @param OperationServiceInterface $operationService
     */
    public function __construct(OperationServiceInterface $operationService)
    {
        $this->operationService = $operationService;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableOperations(): Map
    {
        return new Map($this->operationService->getAvailableOperations());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $operations = $this->getAvailableOperations();

        $operationsWithData = array_map(function ($operation) {
            /** @var OperationInterface $operation */
            return [
                'component_data' => (array) $operation::getComponentData(),
                'operation_data' => []
            ];
        }, $operations->toArray());

        return [
            "available_operations" => $operationsWithData,
        ];
    }
}
