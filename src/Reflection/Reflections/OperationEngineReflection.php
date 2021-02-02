<?php

namespace OpenDialogAi\Core\Reflection\Reflections;


use Ds\Map;
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
        return [
            "available_operations" => $this->getAvailableOperations()->toArray(),
        ];
    }
}
