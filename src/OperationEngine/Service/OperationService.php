<?php

namespace OpenDialogAi\OperationEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\OperationEngine\Exceptions\OperationNotRegisteredException;
use OpenDialogAi\OperationEngine\OperationInterface;

class OperationService implements OperationServiceInterface
{
    /*
     * @var OperationInterface[]
     */
    private $availableOperations = [];

    /** @var AttributeResolver */
    private $attributeResolver;

    /** @var ContextService */
    private $contextService;

    /**
     * @param AttributeResolver $attributeResolver
     */
    public function setAttributeResolver(AttributeResolver $attributeResolver): void
    {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * @param ContextService $contextService
     */
    public function setContextService(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableOperations(): array
    {
        return $this->availableOperations;
    }

    /**
     * @inheritdoc
     */
    public function getOperation($operationName): OperationInterface
    {
        if ($this->isOperationAvailable($operationName)) {
            Log::debug(sprintf("Getting operation with name %s", $operationName));
            return $this->availableOperations[$operationName];
        }

        throw new OperationNotRegisteredException("Operation with name $operationName is not available");
    }

    /**
     * @inheritdoc
     */
    public function isOperationAvailable(string $operationName): bool
    {
        if (in_array($operationName, array_keys($this->getAvailableOperations()))) {
            Log::debug(sprintf("Operation with name %s is available", $operationName));
            return true;
        }

        Log::debug(sprintf("Operation with name %s is not available", $operationName));
        return false;
    }

    public function registerAvailableOperations($operations): void
    {
        /** @var OperationInterface $operation */
        foreach ($operations as $operation) {
            $name = $operation::getName();

            $this->availableOperations[$name] = new $operation();
        }
    }

    public function checkCondition(Condition $condition)
    {
        $attributes = [];

        foreach ($condition->getOperationAttributes() as $name => $attribute) {
            [$contextId, $attributeName] = ContextParser::determineContextAndAttributeId($attribute);

            try {
                $actualAttribute = $this->contextService->getAttribute($attributeName, $contextId);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
                // If the attribute does not exist create one with a null value since we may be testing
                // for its existence.
                $actualAttribute = $this->attributeResolver->getAttributeFor($attributeName, null);
            }

            $attributes[$name] = $actualAttribute;
        }

        $operation = $this->getOperation($condition->getEvaluationOperation());

        $operation->setParameters($condition->getParameters());
        $operation->setAttributes($attributes);

        return $operation->execute();
    }
}
