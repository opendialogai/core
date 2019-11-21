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
        if (is_array($value)) {
            $this->value = htmlspecialchars(json_encode($value), ENT_QUOTES);
        } else {
            $this->value = $value;
        }
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

        try {
            foreach ($index as $key => $value) {
                $arrayValue = $arrayValue[$value];
            }
        } catch (\Exception $e) {
            Log::warning("Undefined offset while getting array value");
            return null;
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

    public function getSerialized(): string
    {
        return $this->value;
    }
}
