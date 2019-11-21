<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * String implementation of Attribute.
 */
class StringAttribute extends BasicAttribute
{
    public static $type = 'attribute.core.string';

    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * Returns null or an strval
     *
     * @param array $arg
     *
     * @return null | string
     */
    public function getValue(array $arg = [])
    {
        return $this->value === null ? $this->value : strval($this->value);
    }
}
