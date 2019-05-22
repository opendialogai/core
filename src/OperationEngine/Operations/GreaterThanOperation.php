<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\OperationEngine\AbstractOperation;

class GreaterThanOperation extends AbstractOperation
{
    const NAME = 'gt';

    public function execute()
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
