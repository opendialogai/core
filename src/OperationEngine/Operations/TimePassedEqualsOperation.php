<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class TimePassedEqualsOperation extends BaseOperation
{
    static $name  = 'time_passed_equals';

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $attribute = reset($this->attributes);

        if ($attribute->getValue() === null) {
            return false;
        }

        if ((now()->timestamp - $this->parameters['value']) === $attribute->getValue()) {
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
