<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\OperationEngine\AbstractOperation;

class IsSetOperation extends AbstractOperation
{
    const NAME = 'is_set';

    public function execute(AttributeInterface $attribute, array $parameters)
    {
        return $attribute->getValue() !== null && $attribute->getValue() !== '';
    }

    public static function getAllowedParameters(): array
    {
        return [];
    }
}
