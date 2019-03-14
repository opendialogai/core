<?php


namespace OpenDialogAi\Core\Attribute;


/**
 * Class BasicAttribute
 * @package OpenDialogAi\Core\Attribute
 *
 * BasicAttribute is a simple implementation of the AttributeInterface that
 * falls back on what PHP would do for comparisons and does not force any
 * specific type.
 */
class BasicAttribute extends AbstractAttribute
{
    /**
     * @param AttributeInterface $attribute
     * @param string $operation
     * @return bool
     * @throws UnsupportedAttributeTypeException
     */
    public function compare(AttributeInterface $attribute, string $operation): bool
    {
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
