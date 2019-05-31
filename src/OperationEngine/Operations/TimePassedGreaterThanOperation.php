<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\AbstractOperation;

class TimePassedGreaterThanOperation extends AbstractOperation
{
    const NAME = 'time_passed_greater_than';

    public function execute()
    {
        $attribute = reset($this->attributes);

        if ($this->parameters['value'] && (now()->timestamp - $this->parameters['value']) > $attribute->getValue()) {
            return true;
        }
        return false;
    }

    public static function getAllowedParameters(): array
    {
        return [
        ];
    }
}
