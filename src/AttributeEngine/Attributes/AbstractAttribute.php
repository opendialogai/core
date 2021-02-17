<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\Core\Components\Contracts\OpenDialogComponent;
use OpenDialogAi\Core\Components\ODComponent;
use OpenDialogAi\Core\Components\ODComponentTypes;

/**
 * Abstract class implementation of the AttributeInterface.
 */
abstract class AbstractAttribute implements Attribute, OpenDialogComponent
{
    use ODComponent;

    protected static string $componentType = ODComponentTypes::ATTRIBUTE_TYPE_COMPONENT_TYPE;

    const UNDEFINED_CONTEXT = 'undefined_context';
    const INVALID_ATTRIBUTE_NAME = 'invalid_attribute_name';

    /* @var string $id - the attribute id. */
    protected string $id;

    /**
     * AbstractAttribute constructor.
     * @param $id
     * @param $value
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public static function getType(): string
    {
        return static::$componentId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return AbstractAttribute
     */
    public function copy(): AbstractAttribute
    {
        return clone $this;
    }

    public function jsonSerialize()
    {
        return [
            'attribute_id' => $this->getId(),
            'attribute_type_id' => $this->getType(),
            'attributeValue' => json_encode($this->getValue())
        ];
    }
}
