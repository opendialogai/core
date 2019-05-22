<?php

namespace OpenDialogAi\OperationEngine;

abstract class AbstractOperation implements OperationInterface
{
    protected $attributes;

    protected $parameters;

    public function __construct($attributes = [], $parameters = [])
    {
        $this->attributes = $attributes;
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
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
    public function getParameters()
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
