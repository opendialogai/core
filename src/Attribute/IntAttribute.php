<?php


namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * Int implementation of Attribute.
 */
class IntAttribute extends AbstractAttribute
{
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::INT, $value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }

    public function getValue()
    {
        return intval(parent::getValue());
    }

    /**
     * @param AttributeInterface $attribute
     * @param string $operation
     * @return bool
     * @throws UnsupportedAttributeTypeException
     */
    public function compare(AttributeInterface $attribute, string $operation): bool
    {
        if (!($attribute instanceof IntAttribute)) {
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
                break;
            case AbstractAttribute::GREATER_THAN:
                return $this->testGreaterThan($attribute);
                break;
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
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function testGreaterThan(AttributeInterface $attribute)
    {
        if ($this->getValue() > $attribute->getValue()) {
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
