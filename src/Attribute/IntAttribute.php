<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;
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
 * Int implementation of Attribute.
 */
class IntAttribute extends BasicAttribute
{
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::INT, $value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }

    public function getValue()
    {
        return intval(parent::getValue());
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
