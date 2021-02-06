<?php


namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\Core\Components\BaseOpenDialogComponent;

class ExampleCustomAttributeType extends StringAttribute
{
    /**
     * @var string
     */
    public static $attributeType = 'attribute.app.custom';

    protected static ?string $componentName = 'Example attribute type';
    protected static ?string $componentDescription = 'Just an example attribute type.';

    protected static string $componentSource = BaseOpenDialogComponent::APP_COMPONENT_SOURCE;

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
