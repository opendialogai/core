<?php


namespace OpenDialogAi\AttributeEngine\Contracts;

interface ScalarAttribute extends Attribute
{
    /**
     * @param AttributeValue $value
     */
    public function setAttributeValue(AttributeValue $value);

    /**
     * @return AttributeValue
     */
    public function getAttributeValue(): ?AttributeValue;

    /**
     * @param $rawValue
     * @return mixed
     */
    public function setRawValue($rawValue);
}
