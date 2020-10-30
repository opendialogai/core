<?php

namespace OpenDialogAi\OperationEngine\Tests\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class DummyOperation extends BaseOperation
{
    public static $name = 'dummy';

    public function performOperation() : bool
    {
        return true;
    }

    public static function getAllowedParameters(): array
    {
        return [];
    }
}
