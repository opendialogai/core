<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\AbstractOperation;

class GreaterThanOperation extends AbstractOperation
{
    static $name = 'gt';

    public function execute()
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() > $this->parameters['value']) {
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
