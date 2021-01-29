<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

/**
 * String implementation of Attribute.
 */
class StringAttribute extends BasicAttribute
{

    /**
     * @var string
     */
    public static $type = 'attribute.core.string';

    /**
     * StringAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * Returns null or an strval
     *
     * @param array $arg
     *
     * @return null | string
     */
    public function getValue(array $arg = [])
    {
        return $this->value === null ? $this->value : strval($this->value);
    }
}
