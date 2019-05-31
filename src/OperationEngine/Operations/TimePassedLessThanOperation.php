<?php

namespace OpenDialogAi\OperationEngine\Operations;

use OpenDialogAi\OperationEngine\AbstractOperation;

class TimePassedLessThanOperation extends AbstractOperation
{
    const NAME = 'time_passed_less_than';

    public function execute()
    {
        $attribute = reset($this->attributes);

        if ($this->parameters['value'] && (now()->timestamp - $this->parameters['value']) < $attribute->getValue()) {
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
