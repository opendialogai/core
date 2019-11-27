<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A ArrayAttribute implementation.
 */
class ArrayAttribute extends AbstractAttribute
{

    /**
     * @var string
     */
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
     */
    public function setValue($value)
    {
        if (is_string($value)) {
            $value = json_decode(htmlspecialchars_decode($value), true);
        }

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

        try {
            foreach ($index as $key => $value) {
                $arrayValue = $arrayValue[$value];
            }
        } catch (\Exception $e) {
            Log::warning("Undefined offset while getting array value");
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

    /**
     * @return string
     */
    public function serialized(): string
    {
        return $this->value;
    }
}
