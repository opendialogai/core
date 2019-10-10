<?php

namespace OpenDialogAi\OperationEngine;

use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Traits\HasName;

abstract class BaseOperation implements OperationInterface
{
    use HasName;

    /**
     * @var AttributeInterface[]
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $parameters;

    protected static $name = 'base';

    public function __construct($attributes = [], $parameters = [])
    {
        $this->attributes = $attributes;
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($attributes): OperationInterface
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function setParameters($parameters): OperationInterface
    {
        $this->parameters = $parameters;
        return $this;
    }
}
