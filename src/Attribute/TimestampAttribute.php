<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A TimestampAttribute implementation.
 */
class TimestampAttribute extends AbstractAttribute
{
    /**
     * TimestampAttribute constructor.
     * @param $id
     * @param $value
     * @throws UnsupportedAttributeTypeException
     */
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::TIMESTAMP, $this->value);
            $this->setValue($value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
        }
    }

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
