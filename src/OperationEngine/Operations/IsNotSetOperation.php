<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\OperationEngine\BaseOperation;

class IsNotSetOperation extends BaseOperation
{
    public static ?string $componentId  = 'is_not_set';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

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
