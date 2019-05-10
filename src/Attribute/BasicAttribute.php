<?php

namespace OpenDialogAi\Core\Attribute;

use OpenDialogAi\Core\Attribute\Operation\EquivalenceOperation;
use OpenDialogAi\Core\Attribute\Operation\GreaterThanOperation;
use OpenDialogAi\Core\Attribute\Operation\GreaterThanOrEqualOperation;
use OpenDialogAi\Core\Attribute\Operation\InSetOperation;
use OpenDialogAi\Core\Attribute\Operation\IsNotSetOperation;
use OpenDialogAi\Core\Attribute\Operation\IsSetOperation;
use OpenDialogAi\Core\Attribute\Operation\LessThanOperation;
use OpenDialogAi\Core\Attribute\Operation\LessThanOrEqualOperation;
use OpenDialogAi\Core\Attribute\Operation\NotInSetOperation;
use OpenDialogAi\Core\Attribute\Operation\OperationInterface;

/**
 * BasicAttribute is a simple implementation of the AttributeInterface that
 * falls back on what PHP would do for comparisons and does not force any
 * specific type.
 */
class BasicAttribute extends AbstractAttribute
{
    /**
     * @return array
     */
    public function allowedAttributeOperations()
    {
        return [
            EquivalenceOperation::NAME,
            GreaterThanOperation::NAME,
            GreaterThanOrEqualOperation::NAME,
            InSetOperation::NAME,
            IsNotSetOperation::NAME,
            IsSetOperation::NAME,
            LessThanOperation::NAME,
            LessThanOrEqualOperation::NAME,
            NotInSetOperation::NAME,
        ];
    }

    /**
     * @param OperationInterface $operation
     * @return bool
     * @throws UnsupportedAttributeTypeException
     */
    public function executeOperation(OperationInterface $operation, $parameters = []): bool
    {
        return $operation->execute($this, $parameters);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
