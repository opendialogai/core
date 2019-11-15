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
     * @return null | string
     */
    public function getValue()
    {
        return $this->value === null ? $this->value : strval($this->value);
    }
}
