<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * Float implementation of Attribute.
 */
class FloatAttribute extends BasicAttribute
{
    public function __construct($id, $value)
    {
        parent::__construct($id, AbstractAttribute::FLOAT, $value);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
