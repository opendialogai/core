<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class NotInSetOperation extends BaseOperation
{
    public static $name  = 'not_in_set';

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $attribute = reset($this->attributes);

        return !in_array($this->parameters['value'], $attribute->getValue());
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
