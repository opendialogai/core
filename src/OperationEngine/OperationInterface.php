<?php

namespace OpenDialogAi\OperationEngine;

use OpenDialogAi\Core\Attribute\AttributeInterface;

interface OperationInterface
{
    public function execute(AttributeInterface $attribute, array $parameters);

    public static function getAllowedParameters(): array;
}
