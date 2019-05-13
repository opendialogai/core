<?php

namespace OpenDialogAi\Core\Attribute;

use Illuminate\Support\Facades\Log;

/**
 * A TimestampAttribute implementation.
 */
class TimestampAttribute extends AbstractAttribute
{
    /**
     * TimestampAttribute constructor.
     * @param $id
     * @param $value
     * @throws UnsupportedAttributeTypeException
     */
    public function __construct($id, $value)
    {
        try {
            parent::__construct($id, AbstractAttribute::TIMESTAMP, $this->value);
            $this->setValue($value);
        } catch (UnsupportedAttributeTypeException $e) {
            Log::warning($e->getMessage());
            return null;
        }
    }

    public function setValue($value)
    {
        $this->value = filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * @param AttributeInterface $attribute
     * @param string $operation
     * @return bool
     * @throws UnsupportedAttributeTypeException
     */
    public function compare(AttributeInterface $attribute, string $operation): bool
    {
        if (!($attribute instanceof TimestampAttribute)) {
            throw new UnsupportedAttributeTypeException(
                sprintf('Trying to compare type %s to type %s', $this->getType(), $attribute->getType())
            );
        }

        switch ($operation) {
            case AbstractAttribute::TIME_PASSED_GREATER_THAN:
                return $this->testGreaterThan($attribute);
                break;
            case AbstractAttribute::TIME_PASSED_LESS_THAN:
                return $this->testLessThan($attribute);
                break;
            case AbstractAttribute::TIME_PASSED_EQUALS:
                return $this->testEquivalence($attribute);
                break;
            default:
                return false;
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function testGreaterThan(AttributeInterface $attribute)
    {
        if ((now()->timestamp - $this->getValue()) > $attribute->getValue()) {
            return true;
        }
        return false;
    }

    /**
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function testLessThan(AttributeInterface $attribute)
    {
        if ((now()->timestamp - $this->getValue()) < $attribute->getValue()) {
            return true;
        }
        return false;
    }

    /**
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function testEquivalence(AttributeInterface $attribute)
    {
        if ((now()->timestamp - $this->getValue()) === $attribute->getValue()) {
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
