<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * String implementation of Attribute.
 */
class StringAttribute extends AbstractAttribute
{
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::STRING, $value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string $operation
     * @return bool
     * @throws UnsupportedAttributeTypeException
     */
    public function compare(AttributeInterface $attribute, string $operation): bool
    {
        if (!($attribute instanceof StringAttribute)) {
            throw new UnsupportedAttributeTypeException(
                sprintf('Trying to compare type %s to type %s', $this->getType(), $attribute->getType())
            );
        }

        switch ($operation) {
            case AbstractAttribute::EQUIVALENCE:
                return $this->testEquivalence($attribute);
                break;
            case AbstractAttribute::IS_SET:
                return $this->testIsSet($attribute);
                break;
            case AbstractAttribute::IS_NOT_SET:
                return $this->testIsNotSet($attribute);
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
    private function testIsSet(AttributeInterface $attribute)
    {
        if ($attribute->getValue() === null) {
            return false;
        }

        return true;
    }

    /**
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function testIsNotSet(AttributeInterface $attribute)
    {
        if ($attribute->getValue() === null) {
            return true;
        }

        return false;
    }


    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->getValue();
    }
}
