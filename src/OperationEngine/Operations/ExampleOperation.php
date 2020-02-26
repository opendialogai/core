<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class ExampleOperation extends BaseOperation
{
    public static $name = 'example';

    /**
     * @inheritDoc
     */
    public function performOperation() : bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() > $this->parameters['start_value'] &&
            $attribute->getValue() < $this->parameters['end_value']) {
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
                'start_value',
                'end_value',
            ],
        ];
    }
}
