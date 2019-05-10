<?php

namespace OpenDialogAi\Core\Attribute\Condition;

use Ds\Map;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Attribute\HasAttributesTrait;
use OpenDialogAi\Core\Attribute\Operation\EquivalenceOperation;
use OpenDialogAi\Core\Attribute\Operation\GreaterThanOperation;
use OpenDialogAi\Core\Attribute\Operation\GreaterThanOrEqualOperation;
use OpenDialogAi\Core\Attribute\Operation\InSetOperation;
use OpenDialogAi\Core\Attribute\Operation\IsNotSetOperation;
use OpenDialogAi\Core\Attribute\Operation\IsSetOperation;
use OpenDialogAi\Core\Attribute\Operation\LessThanOperation;
use OpenDialogAi\Core\Attribute\Operation\LessThanOrEqualOperation;
use OpenDialogAi\Core\Attribute\Operation\NotInSetOperation;

/**
 * Implementation of the Condition interface for Attributes.
 */
class Condition implements ConditionInterface
{
    use HasAttributesTrait, ConditionTrait;

    public function __construct($evaluationOperation, $parameters = [])
    {
        $this->parameters = $parameters;

        if ($operationClass = $this->getOperationClass($evaluationOperation)) {
            $this->evaluationOperation = new $operationClass;
        } else {
            throw new \Exception();
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @return bool
     */
    public function executeOperation(AttributeInterface $attribute)
    {
        return $attribute->executeOperation($this->evaluationOperation, $this->parameters);
    }

    /**
     * @return string|null
     */
    protected function getOperationClass($operationName)
    {
        $operations = [
            EquivalenceOperation::NAME => EquivalenceOperation::class,
            GreaterThanOperation::NAME => GreaterThanOperation::class,
            GreaterThanOrEqualOperation::NAME => GreaterThanOrEqualOperation::class,
            InSetOperation::NAME => InSetOperation::class,
            IsNotSetOperation::NAME => IsNotSetOperation::class,
            IsSetOperation::NAME => IsSetOperation::class,
            LessThanOperation::NAME => LessThanOperation::class,
            LessThanOrEqualOperation::NAME => LessThanOrEqualOperation::class,
            NotInSetOperation::NAME => NotInSetOperation::class,
        ];

        if (isset($operations[$operationName])) {
            return $operations[$operationName];
        }

        return null;
    }
}
