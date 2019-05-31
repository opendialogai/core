<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\AbstractOperation;

class NotInSetOperation extends AbstractOperation
{
    const NAME = 'not_in_set';

    public function execute()
    {
        $attribute = reset($this->attributes);

        return !in_array($this->parameters['value'], $attribute->getValue());
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
