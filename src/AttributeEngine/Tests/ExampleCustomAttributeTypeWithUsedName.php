<?php


namespace OpenDialogAi\AttributeEngine\Tests;


use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;

class ExampleCustomAttributeTypeWithUsedName extends StringAttribute
{
    /**
     * @var string
     */
    public static $type = 'attribute.core.string';

    /**
     * ExampleCustomAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * Returns null or an strval prepended with 'custom: '
     *
     * @param array $arg
     *
     * @return null | string
     */
    public function getValue(array $arg = [])
    {
        $stringValue = parent::getValue($arg);
        return is_null($stringValue) ? null : ('custom: ' . $stringValue);
    }
}
