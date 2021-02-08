<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\OperationEngine\BaseOperation;

class TimePassedEqualsOperation extends BaseOperation
{
    public static ?string $componentId  = 'time_passed_equals';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() === null) {
            return false;
        }

        if ((now()->timestamp - $this->parameters['value']) === $attribute->getValue()) {
            return true;
        }
        return false;
    }
}
