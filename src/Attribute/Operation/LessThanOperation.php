<?php

namespace OpenDialogAi\Core\Attribute\Operation;

use OpenDialogAi\Core\Attribute\AttributeInterface;

class LessThanOperation extends AbstractOperation
{
    const NAME = 'lt';

    public function execute(AttributeInterface $attribute, array $parameters)
    {
        if ($attribute->getValue() < $parameters['value']) {
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
