<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\OperationEngine\BaseOperation;

class ExampleOperation extends BaseOperation
{
    public static $name = 'example';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    protected static array $requiredParametersArgumentNames = [
        'start_value',
        'end_value',
    ];

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() > $this->parameters['start_value'] &&
            $attribute->getValue() < $this->parameters['end_value']) {
            return true;
        }
        return false;
    }
}
