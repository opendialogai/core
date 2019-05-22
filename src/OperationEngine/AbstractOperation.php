<?php

namespace OpenDialogAi\OperationEngine;

abstract class AbstractOperation implements OperationInterface
{
    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return static::NAME;
    }
}
