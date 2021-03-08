<?php


namespace OpenDialogAi\Core\Conversation;

class Condition
{
    public const OPERATION = 'operation';
    public const OPERATION_ATTRIBUTES = 'operationAttributes';
    public const PARAMETERS = 'parameters';

    //TODO: change me when conditions are updated.
    const FIELDS = [self::OPERATION, self::OPERATION_ATTRIBUTES, self::PARAMETERS];
    protected string $operation;
    protected array $operationAttributes;
    protected ?array $parameters;

    public function __construct(string $operation, array $operationAttributes, ?array $parameters)
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

    public function getParameters(): ?array
    {
        return $this->parameters;
    }
}
