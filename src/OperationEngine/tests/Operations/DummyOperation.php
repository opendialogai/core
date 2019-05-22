<?php

namespace OpenDialogAi\OperationEngine\tests\Operations;

use OpenDialogAi\OperationEngine\AbstractOperation;

class DummyOperation extends AbstractOperation
{
    const NAME = 'dummy';

    public function execute()
    {
    }

    public static function getAllowedParameters(): array
    {
        return [];
    }
}
