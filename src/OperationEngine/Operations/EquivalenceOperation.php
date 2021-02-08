<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\OperationEngine\BaseOperation;

class EquivalenceOperation extends BaseOperation
{
    public static ?string $componentId = 'eq';

    protected static ?string $componentName = 'Equals';
    protected static ?string $componentDescription
        = 'An operation that determines if the given attribute has a value equal the given parameter.';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() === $this->parameters['value']) {
            return true;
        }
        return false;
    }
}
