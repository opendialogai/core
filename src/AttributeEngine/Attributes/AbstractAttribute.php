<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;

/**
 * Abstract class implementation of the AttributeInterface.
 */
abstract class AbstractAttribute implements Attribute
{
    const UNDEFINED_CONTEXT = 'undefined_context';
    const INVALID_ATTRIBUTE_NAME = 'invalid_attribute_name';

    /* @var string $id - a unique id for this attribute class. */
    protected $id;

    /* @var string $type - one of the valid string types. */
    public static $attributeType;

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
        return static::$attributeType;
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
            'name' => $this->getId(),
            'attributeValue' => json_encode($this->getValue())
        ];
    }
}
