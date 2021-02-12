<?php


namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\Core\Components\ODComponentTypes;

class ExampleCustomAttributeType extends StringAttribute
{
    public static ?string $componentId = 'attribute.app.custom';

    protected static ?string $componentName = 'Example attribute type';
    protected static ?string $componentDescription = 'Just an example attribute type.';

    protected static string $componentSource = ODComponentTypes::APP_COMPONENT_SOURCE;

    /**
     * ExampleCustomAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, StringAttributeValue $value = null)
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
