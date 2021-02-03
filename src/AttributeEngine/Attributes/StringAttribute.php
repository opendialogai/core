<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;

/**
 * String implementation of Attribute.
 */
class StringAttribute extends BasicAttribute
{
    /**
     * @var string
     */
    public static $type = 'attribute.core.string';

    protected static ?string $componentName = 'String';
    protected static ?string $componentDescription = 'An attribute type for representing strings.';

    protected static string $componentSource = BaseOpenDialogComponent::CORE_COMPONENT_SOURCE;

    /**
     * StringAttribute constructor.
     *
     * @param $id
     * @param $value
     */
    public function __construct($id, $value)
    {
        parent::__construct($id, $value);
    }

    /**
     * Returns null or an strval
     *
     * @param array $arg
     *
     * @return null | string
     */
    public function getValue(array $arg = [])
    {
        return $this->value === null ? $this->value : strval($this->value);
    }
}
