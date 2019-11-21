<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A ArrayAttribute implementation.
 */
class ArrayAttribute extends AbstractAttribute
{
    public static $type = 'attribute.core.array';

    /**
     * ArrayAttribute constructor.
     * @param $id
     * @param $value
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $this->value);
        $this->setValue($value);
    }

    public function setValue($value)
    {
        $this->value = htmlspecialchars(json_encode($value), ENT_QUOTES);
    }

    public function getValue(array $index = [])
    {
        if (!$index) {
            return json_decode(htmlspecialchars_decode($this->value));
        }
        return json_decode(htmlspecialchars_decode($this->value), true)[(int) $index[0]];
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }
}
