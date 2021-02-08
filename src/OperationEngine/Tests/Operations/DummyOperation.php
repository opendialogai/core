<?php

namespace OpenDialogAi\OperationEngine\Tests\Operations;

use OpenDialogAi\OperationEngine\BaseOperation;

class DummyOperation extends BaseOperation
{
    public static ?string $componentId = 'dummy';

    protected static ?string $componentName = 'Example operation';
    protected static ?string $componentDescription = 'Just an example operation.';

    protected static array $requiredParametersArgumentNames = [];

    public function performOperation() : bool
    {
        return true;
    }
}
