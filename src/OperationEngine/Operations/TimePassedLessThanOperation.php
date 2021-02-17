<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\ODComponentTypes;
use OpenDialogAi\OperationEngine\BaseOperation;

class TimePassedLessThanOperation extends BaseOperation
{
    protected static string $componentId = 'time_passed_less_than';


    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        // We are checking for type since the default behaviour is to return an empty string if an attribute
        // is not set, which would allow this operation to proceed.
        if (($attribute->getValue() === null) || !is_int($attribute->getValue())) {
            return false;
        }

        if ($this->parameters['value'] && (now()->timestamp - $this->parameters['value']) < $attribute->getValue()) {
            return true;
        }
        return false;
    }
}
