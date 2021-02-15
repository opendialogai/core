<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\ODComponentTypes;
use OpenDialogAi\OperationEngine\BaseOperation;

class LessThanOrEqualOperation extends BaseOperation
{
    protected static ?string $componentId = 'lte';

    protected static string $componentSource = ODComponentTypes::CORE_COMPONENT_SOURCE;

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() <= $this->parameters['value']) {
            return true;
        }
        return false;
    }
}
