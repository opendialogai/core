<?php


namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;

class ExampleCustomAttributeTypeWithUsedName extends StringAttribute
{
    /**
     * @var string
     */
    public static $attributeType = 'attribute.core.string';

    /**
     * ExampleCustomAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, StringAttributeValue $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * Returns null or an strval prepended with 'custom: '
     *
     * @return null | string
     */
    public function toString(): ?string
    {
        $stringValue = parent::toString();
        return is_null($stringValue) ? null : ('custom: ' . $stringValue);
    }
}
