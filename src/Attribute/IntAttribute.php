<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * Int implementation of Attribute.
 */
class IntAttribute extends BasicAttribute
{
    public static $type = 'attribute.core.int';

    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * Returns null or an intval
     *
     * @return mixed|void
     */
    public function getValue()
    {
        return $this->value === null ? $this->value : intval($this->value);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
