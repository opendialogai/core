<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;
use OpenDialogAi\OperationEngine\BaseOperation;

class IsSetOperation extends BaseOperation
{
    public static $name  = 'is_set';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        return $attribute->getValue() !== null && $attribute->getValue() !== '' && $attribute->getValue() !== false;
    }

    /**
     * @inheritDoc
     */
    public static function getAllowedParameters(): array
    {
        return [];
    }
}
