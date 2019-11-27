<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A BooleanAttribute implementation.
 */
class BooleanAttribute extends AbstractAttribute
{

    /**
     * @var string
     */
    public static $type = 'attribute.core.boolean';

    /**
     * BooleanAttribute constructor.
     * @param $id
     * @param $value
     * @throws UnsupportedAttributeTypeException
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $this->value);
        $this->setValue($value);
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = is_null($value) ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->getValue() ? 'true' : 'false';
    }

    /**
     * Returns boolean
     *
     * @param array $arg
     *
     * @return boolean
     */
    public function getValue(array $arg = [])
    {
        return $this->value === null ? $this->value :  boolval($this->value);
    }
}
