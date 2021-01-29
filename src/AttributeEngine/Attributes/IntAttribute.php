<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

/**
 * Int implementation of Attribute.
 */
class IntAttribute extends BasicAttribute
{

    /**
     * @var string
     */
    public static $type = 'attribute.core.int';

    /**
     * IntAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * Returns null or an intval
     *
     * @param array $arg
     *
     * @return int | null
     */
    public function getValue(array $arg = [])
    {
        return $this->value === null ? $this->value : intval($this->value);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
