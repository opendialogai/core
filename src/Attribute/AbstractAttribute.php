<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * Abstract class implementation of the AttributeInterface.
 */
abstract class AbstractAttribute implements AttributeInterface
{
    const UNDEFINED_CONTEXT = 'undefined_context';
    const INVALID_ATTRIBUTE_NAME = 'invalid_attribute_name';

    /* @var string $id - a unique id for this attribute class. */
    protected $id;

    /* @var string $type - one of the valid string types. */
    public static $type;

    /* @var mixed $value - the value for this attribute. */
    protected $value;

    /**
     * AbstractAttribute constructor.
     * @param $id
     * @param $type
     * @param $value
     */
    public function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType(): string
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
     * @return string
     */
    public function getSerialized(): string
    {
        return $this->value;
    }
}
