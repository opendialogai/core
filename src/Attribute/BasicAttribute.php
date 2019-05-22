<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * BasicAttribute is a simple implementation of the AttributeInterface that
 * falls back on what PHP would do for comparisons and does not force any
 * specific type.
 */
class BasicAttribute extends AbstractAttribute
{
    /**
     * @param string $operation
     * @return bool
     * @throws UnsupportedAttributeTypeException
     */
    public function executeOperation($operation, $parameters = []): bool
    {
        return $operation->execute($this, $parameters);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
