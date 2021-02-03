<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\OperationEngine\BaseOperation;

class GreaterThanOrEqualOperation extends BaseOperation
{
    public static $name = 'gte';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() >= $this->parameters['value']) {
            return true;
        }
        return false;
    }
}
