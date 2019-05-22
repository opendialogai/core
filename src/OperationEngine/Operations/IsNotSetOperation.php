<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\OperationEngine\AbstractOperation;

class IsNotSetOperation extends AbstractOperation
{
    const NAME = 'is_not_set';

    public function execute()
    {
        return $attribute->getValue() === null || $attribute->getValue() === '';
    }

    public static function getAllowedParameters(): array
    {
        return [];
    }
}
