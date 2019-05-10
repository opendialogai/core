<?php

namespace OpenDialogAi\Core\Attribute\Operation;

use OpenDialogAi\Core\Attribute\AttributeInterface;

class GreaterThanOperation extends AbstractOperation
{
    const NAME = 'gt';

    public function execute(AttributeInterface $attribute, array $parameters)
    {
        if ($attribute->getValue() > $parameters['value']) {
            return true;
        }
        return false;
    }

    public static function getAllowedParameters(): array
    {
        return [
            'required' => [
                'value',
            ],
        ];
    }
}
