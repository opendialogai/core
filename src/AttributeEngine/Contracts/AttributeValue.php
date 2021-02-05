<?php


namespace OpenDialogAi\AttributeEngine\Contracts;

/**
 * An attribute value is a value that can be stored against a scalar attribute.
 */
interface AttributeValue extends \JsonSerializable
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getRawValue();

    /**
     * @return mixed
     */
    public function setRawValue($rawValue);

    /**
     * @return mixed
     */
    public function getTypedValue();

    /**
     * @return string|null
     */
    public function toString(): ?string;
}
