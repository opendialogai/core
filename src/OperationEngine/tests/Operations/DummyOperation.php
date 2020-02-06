<?php

namespace OpenDialogAi\OperationEngine\tests\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class DummyOperation extends BaseOperation
{
    public static $name = 'dummy';

    public function performOperation() : bool
    {
    }

    public static function getAllowedParameters(): array
    {
        return [];
    }
}
