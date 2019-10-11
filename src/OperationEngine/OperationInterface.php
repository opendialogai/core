<?php

namespace OpenDialogAi\OperationEngine;

interface OperationInterface
{
    /**
     * Run the operation and return the result. True means all conditions are met
     *
     * @return bool
     */
    public function execute(): bool;

    public function getAttributes(): array;

    public function setAttributes($attributes): OperationInterface;

    public function getParameters(): array;

    public function setParameters($parameters): OperationInterface;

    /**
     * Returns an array specifying the allowed parameters for the operations
     *
     * @return array
     */
    public static function getAllowedParameters(): array;

    public static function getName(): string;
}
