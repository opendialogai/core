<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * Float implementation of Attribute.
 */
class FloatAttribute extends BasicAttribute
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

        return $this->doComparison($attribute, $operation);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
