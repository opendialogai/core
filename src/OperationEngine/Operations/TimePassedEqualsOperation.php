<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\AbstractOperation;

class TimePassedEqualsOperation extends AbstractOperation
{
    const NAME = 'time_passed_equals';

    public function execute()
    {
        $attribute = reset($this->attributes);

        if ((now()->timestamp - $this->parameters['value']) === $attribute->getValue()) {
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
