<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * Float implementation of Attribute.
 */
class FloatAttribute extends AbstractAttribute
{
    public function __construct($id, $value)
    {
        parent::__construct($id, AbstractAttribute::FLOAT, $value);
    }

    /**
     * @param AttributeInterface $attribute
     * @param string $operation
     * @return bool
     * @throws UnsupportedAttributeTypeException
     */
    public function compare(AttributeInterface $attribute, string $operation): bool
    {
        if (!($attribute instanceof FloatAttribute)) {
            throw new UnsupportedAttributeTypeException(
                sprintf('Trying to compare type %s to type %s', $this->getType(), $attribute->getType())
            );
        }

        switch ($operation) {
            case AbstractAttribute::EQUIVALENCE:
                return $this->testEquivalence($attribute);
                break;
            case AbstractAttribute::GREATER_THAN_OR_EQUAL:
                return $this->testGreaterThanOrEqual($attribute);
            default:
                return false;
        }
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
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function testGreaterThanOrEqual(AttributeInterface $attribute)
    {
        if ($this->getValue() >= $attribute->getValue()) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
