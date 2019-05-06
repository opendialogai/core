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

    // Operations that can be performed
    const EQUIVALENCE = 'eq';
    const GREATER_THAN = 'gt';
    const LESS_THAN = 'lt';
    const GREATER_THAN_OR_EQUAL = 'gte';
    const LESS_THAN_OR_EQUAL = 'lte';
    const IN_SET = 'in_set';
    const NOT_IN_SET = 'not_in_set';
    const IS_SET = 'is_set';
    const IS_NOT_SET = 'is_not_set';

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
     * @return array
     */
    public static function allowedAttributeOperations()
    {
        return [
            self::EQUIVALENCE,
            self::GREATER_THAN,
            self::LESS_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::LESS_THAN_OR_EQUAL,
            self::IN_SET,
            self::NOT_IN_SET,
            self::IS_SET,
            self::IS_NOT_SET,
        ];
    }

    /**
     * @return array
     */
    public static function operationsNotRequiringValue()
    {
        return [
            self::IS_SET,
            self::IS_NOT_SET
        ];
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
            self::DATETIME
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
