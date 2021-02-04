<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\Core\Components\BaseOpenDialogComponent;

/**
 * Abstract class implementation of the AttributeInterface.
 */
abstract class AbstractAttribute extends BaseOpenDialogComponent implements AttributeInterface
{
    const UNDEFINED_CONTEXT = 'undefined_context';
    const INVALID_ATTRIBUTE_NAME = 'invalid_attribute_name';

    protected static string $componentType = BaseOpenDialogComponent::ATTRIBUTE_TYPE_COMPONENT_TYPE;
    protected static string $componentSource = BaseOpenDialogComponent::APP_COMPONENT_SOURCE;

    /* @var string $id - a unique id for this attribute class. */
    protected $id;

    /* @var string $type - one of the valid string types. */
    public static $type;

    /* @var mixed $value - the value for this attribute. */
    protected $value;

    /**
     * AbstractAttribute constructor.
     * @param $id
     * @param $value
     */
    public function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public static function getName(): string
    {
        return static::$type;
    }

    /**
     * @return string
     */
    public static function getType(): string
    {
        return static::$type;
    }

    /**
     * @param array $arg
     *
     * @return mixed
     */
    public function getValue(array $arg = [])
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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

    /**
     * @return string
     */
    public function serialized(): ?string
    {
        return $this->value;
    }
}
