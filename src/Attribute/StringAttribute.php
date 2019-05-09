<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * String implementation of Attribute.
 */
class StringAttribute extends BasicAttribute
{
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::STRING, $value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }
}
