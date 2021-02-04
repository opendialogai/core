<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;

/**
 * BasicAttribute is a simple implementation of the AttributeInterface that
 * falls back on what PHP would do for comparisons and does not force any
 * specific type.
 */
class BasicAttribute extends AbstractAttribute
{
    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
