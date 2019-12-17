<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class IsSetOperation extends BaseOperation
{
    public static $name  = 'is_set';

    /**
     * @inheritDoc
     */
    public function execute(): bool
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
