<?php


namespace OpenDialogAi\Core\Conversation\Contracts;

use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;

interface Condition
{
    public function getOperation(): string;

    public function setOperation(string $id): void;

    public function getAttributes(): AttributeBag;

    public function setAttributes(AttributeBag $attributes): void;

    public function getParameters(): array;

    public function setParameters(array $parameters);
}
