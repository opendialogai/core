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

    /**
     * @param mixed $value
     *
     * @return mixed|void
     */
    public function setValue($value)
    {
        $this->value = htmlspecialchars(json_encode($value), ENT_QUOTES);
    }

    /**
     * @param array $index
     *
     * @return mixed
     */
    public function getValue(array $index = [])
    {
        if (!$index) {
            return json_decode(htmlspecialchars_decode($this->value), true);
        }

        $arrayValue = json_decode(htmlspecialchars_decode($this->value), true);

        foreach ($index as $key => $value) {
            $arrayValue = $arrayValue[$value];
        }

        return $arrayValue;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }
}
