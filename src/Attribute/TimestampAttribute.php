<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A TimestampAttribute implementation.
 */
class TimestampAttribute extends AbstractAttribute
{

    /**
     * @var string
     */
    public static $type = 'attribute.core.timestamp';

    /**
     * TimestampAttribute constructor.
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
        $this->value = $value === null ? $value : filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->getValue();
    }
}
