<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A BooleanAttribute implementation.
 */
class BooleanAttribute extends AbstractAttribute
{
    /**
     * BooleanAttribute constructor.
     * @param $id
     * @param $value
     * @throws UnsupportedAttributeTypeException
     */
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::BOOLEAN, $this->value);
            $this->setValue($value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }

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
}
