<?php

return [
    'available_operations' => [
        OpenDialogAi\OperationEngine\Operations\EquivalenceOperation::class,
        OpenDialogAi\OperationEngine\Operations\GreaterThanOperation::class,
        OpenDialogAi\OperationEngine\Operations\GreaterThanOrEqualOperation::class,
        OpenDialogAi\OperationEngine\Operations\InSetOperation::class,
        OpenDialogAi\OperationEngine\Operations\IsNotSetOperation::class,
        OpenDialogAi\OperationEngine\Operations\IsSetOperation::class,
        OpenDialogAi\OperationEngine\Operations\LessThanOperation::class,
        OpenDialogAi\OperationEngine\Operations\LessThanOrEqualOperation::class,
        OpenDialogAi\OperationEngine\Operations\NotInSetOperation::class,
    ]
];
