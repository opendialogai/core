<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * Int implementation of Attribute.
 */
class IntAttribute extends BasicAttribute
{
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::INT, $value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
        }
    }

    /**
     * Returns null or an intval
     *
     * @return mixed|void
     */
    public function getValue()
    {
        $this->value === null ? $this->value : intval($this->value);
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
