<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class GreaterThanOperation extends BaseOperation
{
    public static $name = 'gt';

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        if (!$this->checkRequiredParameters()) {
            return false;
        }

        $attribute = reset($this->attributes);

        if ($attribute->getValue() > $this->parameters['value']) {
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
