<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class EquivalenceOperation extends BaseOperation
{
    static $name = 'eq';

    /**
     * @inheritDoc
     */
    public function execute() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() === $this->parameters['value']) {
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
