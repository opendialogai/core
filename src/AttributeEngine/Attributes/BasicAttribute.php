<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

/**
 * BasicAttribute is a simple implementation of the AttributeInterface that
 * falls back on what PHP would do for comparisons and does not force any
 * specific type.
 */
class BasicAttribute extends AbstractAttribute
{
    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
