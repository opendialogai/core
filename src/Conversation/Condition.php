<?php


namespace OpenDialogAi\Core\Conversation;

use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;

class Condition
{
    protected string $operation;
    protected AttributeBag $attributes;
    protected array $parameters;

    public function __construct(string $operation, AttributeBag $attributes, array $parameters)
    {
        $this->operation = $operation;
        $this->$attributes = $attributes;
        $this->parameters = $parameters;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getAttributes(): AttributeBag
    {
        return $this->attributes;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
