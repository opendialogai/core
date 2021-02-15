<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;
use OpenDialogAi\AttributeEngine\Contracts\CollectionAttribute;

/**
 * Abstract class implementation of the CollectionAttribute.
 */
abstract class AbstractCollectionAttribute extends AbstractAttribute implements CollectionAttribute
{

    /* @var array $values - an array of values */
    protected $values;

    /**
     * AbstractCollectionAttribute constructor.
     * @param $id
     * @param $values
     */
    public function __construct($id, $values = null)
    {
        parent::__construct($id);
        isset($values) ? $this->values : $this->values = [];
    }

    /**
     * @param AttributeValue $value
     */
    public function addValue(AttributeValue $value)
    {
        $values[] = $value;
    }

    /**
     * @param int $index
     * @return AttributeValue
     */
    public function getValueAt(int $index): ?AttributeValue
    {
        if (array_key_exists($index, $this->values)) {
            return $this->values[$index];
        }
        return null;
    }

    /**
     * @return array|mixed
     */
    public function getValue()
    {
        return $this->values;
    }
}
