<?php


namespace OpenDialogAi\Core\Conversation;

class Condition
{
    protected string $operation;
    protected array $operationAttributes;
    protected array $parameters;

    public function __construct(string $operation, array $operationAttributes, array $parameters)
    {
        $this->operation = $operation;
        $this->operationAttributes = $operationAttributes;
        $this->parameters = $parameters;
    }

    public function getEvaluationOperation(): string
    {
        return $this->operation;
    }

    public function getOperationAttributes(): array
    {
        return $this->operationAttributes;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
