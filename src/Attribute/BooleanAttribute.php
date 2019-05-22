<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A BooleanAttribute implementation.
 */
class BooleanAttribute extends AbstractAttribute
{
    /**
     * BooleanAttribute constructor.
     * @param $id
     * @param $value
     * @throws UnsupportedAttributeTypeException
     */
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::BOOLEAN, $this->value);
            $this->setValue($value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }

    public function setValue($value)
    {
        $this->value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

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
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function testEquivalence(AttributeInterface $attribute)
    {
        if ($this->getValue() === $attribute->getValue()) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->getValue() ? 'true' : 'false';
    }
}
