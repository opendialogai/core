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

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param $attributes
     * @return OperationInterface
     */
    public function setAttributes($attributes): OperationInterface;

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param $parameters
     * @return OperationInterface
     */
    public function setParameters($parameters): OperationInterface;

    /**
     * @param $parameterName
     * @return bool
     */
    public function hasParameter($parameterName): bool;

    /**
     * Returns an array specifying the allowed parameters for the operations
     *
     * @return array
     */
    public static function getAllowedParameters(): array;

    /**
     * @return string
     */
    public static function getName(): string;
}
