<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\OperationEngine\AbstractOperation;

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
