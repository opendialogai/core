<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\OperationEngine\BaseOperation;

class NotInSetOperation extends BaseOperation
{
    public static $name  = 'not_in_set';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        return !in_array($this->parameters['value'], $attribute->getValue());
    }

    /**
     * @inheritDoc
     */
    public static function getAllowedParameters(): array
    {
        return [
            'required' => [
                'value',
            ],
        ];
    }
}
