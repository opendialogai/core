<?php

namespace OpenDialogAi\ContextEngine;

use OpenDialogAi\Core\Attribute\AbstractAttribute;

/**
 * A breakdown of a parsed attribute name
 */
class ParsedAttributeName
{
    public $context = AbstractAttribute::UNDEFINED_CONTEXT;

    public $attributeId = AbstractAttribute::INVALID_ATTRIBUTE_NAME;

    public $contextId = AbstractAttribute::UNDEFINED_CONTEXT;

    public $accessor = [];

    public $itemNumber;

    public $itemName;

    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    public function setAttributeId($attributeId)
    {
        $this->attributeId = $attributeId;
        return $this;
    }

    public function setItemNumber($itemNumber)
    {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
        return $this;
    }

    public function hasValidAttributeId()
    {
        return $this->attributeId !== AbstractAttribute::INVALID_ATTRIBUTE_NAME;
    }

    public function hasValidContextName()
    {
        return $this->contextId !== AbstractAttribute::UNDEFINED_CONTEXT;
    }

    /**
     * @return mixed
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * @param mixed $contextId
     */
    public function setContextId($contextId)
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
