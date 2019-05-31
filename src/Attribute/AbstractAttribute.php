<?php

namespace OpenDialogAi\Core\Attribute;

/**
 * Abstract class implementation of the AttributeInterface.
 */
abstract class AbstractAttribute implements AttributeInterface
{
    // Attribute types
    const ENTITY = 'attribute.core.entity';
    const STRING = 'attribute.core.string';
    const BOOLEAN = 'attribute.core.boolean';
    const INT = 'attribute.core.int';
    const FLOAT = 'attribute.core.float';
    const DATETIME = 'attribute.core.dateTime';
    const TIMESTAMP = 'attribute.core.timestamp';

    const UNDEFINED_CONTEXT = 'undefined_context';
    const INVALID_ATTRIBUTE_NAME = 'invalid_attribute_name';

    /* @var string $id - a unique id for this attribute class. */
    protected $id;

    /* @var string $type - one of the valid string types. */
    protected $type;

    /* @var mixed $value - the value for this attribute. */
    protected $value;

    /**
     * AbstractAttribute constructor.
     * @param $id
     * @param $type
     * @param $value
     * @throws UnsupportedAttributeTypeException
     */
    public function __construct($id, $type, $value)
    {
        $this->id = $id;
        $this->value = $value;
        $this->checkAndAssignType($type);
    }

    /**
     * @param $type
     * @throws UnsupportedAttributeTypeException
     */
    private function checkAndAssignType($type)
    {
        $types = [
            self::ENTITY,
            self::STRING,
            self::BOOLEAN,
            self::INT,
            self::FLOAT,
            self::DATETIME,
            self::TIMESTAMP
        ];

        if (!in_array($type, $types, true)) {
            throw new UnsupportedAttributeTypeException(sprintf('Type %s is not supported', $type));
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws UnsupportedAttributeTypeException
     */
    public function setType(string $type): void
    {
        $this->checkAndAssignType($type);
    }

    /**
     * @return mixed
     */
    public function getValue()
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
}
