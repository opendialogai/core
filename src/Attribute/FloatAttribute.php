<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * Float implementation of Attribute.
 */
class FloatAttribute extends BasicAttribute
{
    public static $type = 'attribute.core.float';

    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
