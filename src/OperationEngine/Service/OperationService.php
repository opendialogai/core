<?php

namespace OpenDialogAi\OperationEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\ContextParser;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ContextEngine\ParsedAttributeName;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\OperationEngine\Exceptions\OperationNotRegisteredException;
use OpenDialogAi\OperationEngine\OperationInterface;
use OpenDialogAi\OperationEngine\Operations\IsSetOperation;

class OperationService implements OperationServiceInterface
{
    /**
     * @var OperationInterface[]
     */
    private $availableOperations = [];

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
            $actualAttribute = $this->getActualAttribute($condition, $parsedAttributeName);
            $attributes[$name] = $actualAttribute;
        }

        $operation = $this->getOperation($condition->getEvaluationOperation());

        $operation->setParameters($condition->getParameters());
        $operation->setAttributes($attributes);

        return $operation->execute();
    }

    /**
     * @param Condition $condition
     * @param ParsedAttributeName $parsedAttributeName
     * @return AttributeInterface
     */
    private function getActualAttribute(Condition $condition, ParsedAttributeName $parsedAttributeName): AttributeInterface
    {
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
        } catch (AttributeDoesNotExistException $e) {
            Log::debug(
                sprintf(
                    'Trying to get attribute %s from context %s for operation %s but it does not exist. Using a null value',
                    $parsedAttributeName->attributeId,
                    $parsedAttributeName->contextId,
                    $condition->getEvaluationOperation()
                )
            );

            $actualAttribute = $this->getNullValueAttribute($parsedAttributeName);
        }

        return $actualAttribute;
    }

    /**
     * Gets a null value for the given attribute so that the operation can be used to check for existence in a
     * @see IsSetOperation.
     * If the attribute is not bound, a null valued StringAttribute is returned
     * @param ParsedAttributeName $parsedAttributeName
     * @return AttributeInterface
     */
    private function getNullValueAttribute(ParsedAttributeName $parsedAttributeName): AttributeInterface
    {
        try {
            $actualAttribute = AttributeResolver::getAttributeFor($parsedAttributeName->attributeId, null);
        } catch (AttributeIsNotSupported $e) {
            Log::warning(sprintf(
                'Trying to get attribute that has not been bound for condition. Defaulting to String Attribute'
            ));
            $actualAttribute = new StringAttribute($parsedAttributeName->attributeId, null);
        }
        return $actualAttribute;
    }
}
