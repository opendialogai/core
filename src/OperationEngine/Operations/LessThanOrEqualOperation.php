<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\OperationEngine\AbstractOperation;

class LessThanOrEqualOperation extends AbstractOperation
{
    const NAME = 'lte';

    public function execute(AttributeInterface $attribute, array $parameters)
    {
        if ($attribute->getValue() <= $parameters['value']) {
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
