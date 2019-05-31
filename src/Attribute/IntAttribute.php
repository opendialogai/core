<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * Int implementation of Attribute.
 */
class IntAttribute extends BasicAttribute
{
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::INT, $value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
        }
    }

    public function getValue()
    {
        return parent::getValue();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
