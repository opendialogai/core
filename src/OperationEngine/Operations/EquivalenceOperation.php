<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\OperationEngine\AbstractOperation;

class EquivalenceOperation extends AbstractOperation
{
    const NAME = 'eq';

    public function execute()
    {
        if ($attribute->getValue() === $parameters['value']) {
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
