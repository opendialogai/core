<?php

namespace OpenDialogAi\OperationEngine;

use OpenDialogAi\Core\Components\OpenDialogComponentInterface;

interface OperationInterface extends OpenDialogComponentInterface
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
     * @return bool
     */
    public function performOperation(): bool;

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
