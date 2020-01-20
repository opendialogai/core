<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class InSetOperation extends BaseOperation
{
    public static $name  = 'in_set';

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        return in_array($this->parameters['value'], $attribute->getValue());
    }

    /**
     * @inheritDoc
     */
    public static function getAllowedParameters(): array
    {
        return [
            'required' => [
                'value',
            ],
        ];
    }
}
