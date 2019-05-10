<?php

namespace OpenDialogAi\Core\Attribute\Operation;

use OpenDialogAi\Core\Attribute\AttributeInterface;

class NotInSetOperation extends AbstractOperation
{
    const NAME = 'not_in_set';

    public function execute(AttributeInterface $attribute, array $parameters)
    {
        return !in_array($parameters['value'], $attribute->getValue());
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
