<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A ArrayAttribute implementation.
 */
class ArrayAttribute extends AbstractAttribute
{
    /**
     * ArrayAttribute constructor.
     * @param $id
     * @param $value
     * @throws UnsupportedAttributeTypeException
     */
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::ARRAY, $this->value);
            $this->setValue($value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }

    public function setValue($value)
    {
        $this->value = htmlspecialchars(json_encode($value), ENT_QUOTES);
    }

    public function getValue()
    {
        return json_decode(htmlspecialchars_decode($this->value));
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }
}
