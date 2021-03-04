<?php

namespace OpenDialogAi\OperationEngine\Service;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeDoesNotExistException;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextService\ContextParser;
use OpenDialogAi\ContextEngine\ContextService\ParsedAttributeName;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Components\Exceptions\InvalidComponentDataException;
use OpenDialogAi\Core\Components\Exceptions\MissingRequiredComponentDataException;
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
            try {
                $name = $operation::getName();

                /** @var OperationInterface $registeredOperation */
                $registeredOperation = new $operation();
                $registeredOperation::getComponentData();
                $this->availableOperations[$name] = $registeredOperation;
            } catch (MissingRequiredComponentDataException $e) {
                Log::warning(
                    sprintf(
                        "Skipping adding operation %s to list of supported operations as it doesn't have a %s",
                        $operation,
                        $e->data
                    )
                );
            } catch (InvalidComponentDataException $e) {
                Log::warning(
                    sprintf(
                        "Skipping adding operation %s to list of supported operations as its given %s ('%s') is invalid",
                        $operation,
                        $e->data,
                        $e->value
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkCondition(Condition $condition): bool
    {
        $attributes = [];

        foreach ($condition->getOperationAttributes() as $name => $attribute) {
            $parsedAttributeName = ContextParser::parseAttributeName($attribute);
            $actualAttribute = $this->getAttribute($condition, $parsedAttributeName);
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
     * @return \OpenDialogAi\AttributeEngine\Contracts\Attribute
     */
    private function getAttribute(Condition $condition, ParsedAttributeName $parsedAttributeName): Attribute
    {
        try {
            if (!$parsedAttributeName->getAccessor()) {
                $attribute = ContextService::getAttribute(
                    $parsedAttributeName->attributeId,
                    $parsedAttributeName->contextId
                );
            } else {
                $attribute = ContextService::getAttributeValue(
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

            $attribute = $this->getNullValueAttribute($parsedAttributeName);
        }

        return $attribute;
    }

    /**
     * Gets a null value for the given attribute so that the operation can be used to check for existence in a
     * @param ParsedAttributeName $parsedAttributeName
     * @return \OpenDialogAi\AttributeEngine\Attributes\AttributeInterface
     *@see IsSetOperation.
     * If the attribute is not bound, a null valued StringAttribute is returned
     */
    private function getNullValueAttribute(ParsedAttributeName $parsedAttributeName): AttributeInterface
    {
        return AttributeResolver::getAttributeFor($parsedAttributeName->attributeId, null);
    }
}
