<?php

namespace OpenDialogAi\AttributeEngine;

/**
 * Float implementation of Attribute.
 */
class FloatAttribute extends BasicAttribute
{

    /**
     * @var string
     */
    public static $type = 'attribute.core.float';

    /**
     * FloatAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }

    /**
     * Returns float
     *
     * @param array $arg
     *
     * @return float
     */
    public function getValue(array $arg = [])
    {
        return $this->value === null ? $this->value : floatval($this->value);
    }
}
