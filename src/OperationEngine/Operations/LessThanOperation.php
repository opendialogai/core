<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class LessThanOperation extends BaseOperation
{
    public static $name  = 'lt';

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() < $this->parameters['value']) {
            return true;
        }
        return false;
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
