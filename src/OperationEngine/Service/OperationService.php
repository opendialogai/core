<?php

namespace OpenDialogAi\OperationEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\OperationEngine\Exceptions\OperationNotRegisteredException;
use OpenDialogAi\OperationEngine\OperationInterface;

class OperationService implements OperationServiceInterface
{
    /**
     * @var OperationInterface[]
     */
    private $availableOperations = [];

    /** @var AttributeResolver */
    private $attributeResolver;

    /**
     * @inheritDoc
     */
    public function setAttributeResolver(AttributeResolver $attributeResolver): void
    {
        $this->attributeResolver = $attributeResolver;
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

    /**
     * @inheritdoc
     */
    public function registerAvailableOperations($operations): void
    {
        /** @var OperationInterface $operation */
        foreach ($operations as $operation) {
            $name = $operation::getName();

            $this->availableOperations[$name] = new $operation();
        }
    }

    /**
     * @inheritDoc
     */
    public function checkCondition(Condition $condition)
    {
        $attributes = [];

        foreach ($condition->getOperationAttributes() as $name => $attribute) {
            $parsedAttributeName = ContextParser::parseAttributeName($attribute);

            try {
                if (!$parsedAttributeName->getAccessor()) {
                    $actualAttribute = ContextService::getAttribute(
                        $parsedAttributeName->attributeId,
                        $parsedAttributeName->contextId
                    );
                } else {
                    $actualAttribute = ContextService::getAttributeValue(
                        $parsedAttributeName->attributeId,
                        $parsedAttributeName->contextId,
                        $parsedAttributeName->getAccessor()
                    );
                }
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
                // If the attribute does not exist create one with a null value since we may be testing
                // for its existence.
                $actualAttribute = $this->attributeResolver->getAttributeFor($parsedAttributeName->attributeId, null);
            }

            $attributes[$name] = $actualAttribute;
        }

        $operation = $this->getOperation($condition->getEvaluationOperation());

        $operation->setParameters($condition->getParameters());
        $operation->setAttributes($attributes);

        return $operation->execute();
    }
}
