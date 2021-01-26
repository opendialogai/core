<?php

namespace OpenDialogAi\ContextEngine;

use OpenDialogAi\AttributeEngine\Attributes\AbstractAttribute;

/**
 * A breakdown of a parsed attribute name
 */
class ParsedAttributeName
{
    public $attributeId = AbstractAttribute::INVALID_ATTRIBUTE_NAME;

    public $contextId = AbstractAttribute::UNDEFINED_CONTEXT;

    public $accessor = [];

    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;
        return $this;
    }

    /**
     * Checks if the parsed attribute id was valid or not
     *
     * @return bool
     */
    public function hasValidAttributeId(): bool
    {
        return $this->attributeId !== AbstractAttribute::INVALID_ATTRIBUTE_NAME;
    }

    /**
     * Checks if the parsed context name was valid or not
     *
     * @return bool
     */
    public function hasValidContextName(): bool
    {
        return $this->contextId !== AbstractAttribute::UNDEFINED_CONTEXT;
    }

    /**
     * @param mixed $contextId
     * @return ParsedAttributeName
     */
    public function setContextId($contextId): ParsedAttributeName
    {
        $this->contextId = $contextId;
        return $this;
    }

    /**
     * @return array
     */
    public function getAccessor(): array
    {
        return $this->accessor;
    }

    /**
     * @param array $accessor
     */
    public function setAccessor(array $accessor)
    {
        $this->accessor = $accessor;
    }
}
