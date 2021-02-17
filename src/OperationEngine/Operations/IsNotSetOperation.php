<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\ODComponentTypes;
use OpenDialogAi\OperationEngine\BaseOperation;

class IsNotSetOperation extends BaseOperation
{
    protected static string $componentId = 'is_not_set';

    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    protected static array $requiredParametersArgumentNames = [];

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        return $attribute->getValue() === null || $attribute->getValue() === '';
    }
}
