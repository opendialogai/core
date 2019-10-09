<?php

namespace OpenDialogAi\OperationEngine;

interface OperationInterface
{
    public function execute();

    public function getAttributes();

    public function setAttributes($attributes): OperationInterface;

    public function getParameters();

    public function setParameters($parameters): OperationInterface;

    public static function getAllowedParameters(): array;

    public static function getName(): string;
}
