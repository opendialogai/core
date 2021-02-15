<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use OpenDialogAi\AttributeEngine\Contracts\AttributeValue;
use OpenDialogAi\AttributeEngine\Contracts\ScalarAttribute;

abstract class AbstractScalarAttribute extends AbstractAttribute implements ScalarAttribute
{

    /* @var AttributeValue $value - the value for this attribute. */
    protected $value;

    /**
     * AbstractScalarAttribute constructor.
     * @param $id
     * @param $value
     */
    public function __construct(string $id, AttributeValue $value = null)
    {
        parent::__construct($id);
        $this->value = $value;
    }

    public function setAttributeValue(AttributeValue $value)
    {
        $this->value = $value;
    }

    /**
     * @return AttributeValue
     */
    public function getAttributeValue(): ?AttributeValue
    {
        return $this->value;
    }

    public function getValue()
    {
        return $this->getAttributeValue()->getTypedValue();
    }

    /**
     * @return string
     */
    public function serialized(): ?string
    {
        return $this->value;
    }
}
