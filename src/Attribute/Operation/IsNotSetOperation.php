<?php

namespace OpenDialogAi\Core\Attribute\Operation;

use OpenDialogAi\Core\Attribute\AttributeInterface;

class IsNotSetOperation extends AbstractOperation
{
    const NAME = 'is_not_set';

    public function execute(AttributeInterface $attribute, array $parameters)
    {
        return $attribute->getValue() === null || $attribute->getValue() === '';
    }

    public static function getAllowedParameters(): array
    {
        return [];
    }
}
