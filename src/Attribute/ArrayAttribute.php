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
        if (is_array($value)) {
            $this->value = htmlspecialchars(json_encode($value), ENT_QUOTES);
        } else {
            $this->value = $value;
        }
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
        return htmlspecialchars_decode($this->value);
    }

    public function getSerialized(): string
    {
        return $this->value;
    }
}
