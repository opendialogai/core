<?php

namespace OpenDialogAi\AttributeEngine\Attributes;

use Ds\Map;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\AttributeEngine\AttributeBag\HasAttributesTrait;

/**
 * Abstract class implementation of the AttributeInterface.
 */
abstract class AbstractCompositeAttribute extends AbstractAttribute implements CompositeAttribute
{
    use HasAttributesTrait;

    /**
     * AbstractCompositeAttribute constructor.
     * @param $id
     * @param $value
     */
    public function __construct(string $id, Map $attributes = null)
    {
        parent::__construct($id);
        is_null($attributes) ? $this->attributes = new Map() : $this->attributes = $attributes;
    }

    public function getValue()
    {
        return $this->attributes;
    }
}
