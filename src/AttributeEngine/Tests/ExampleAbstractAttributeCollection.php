<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\ArrayAttribute;
use OpenDialogAi\AttributeEngine\Composite\AbstractAttributeCollection;
use OpenDialogAi\AttributeEngine\IntAttribute;
use OpenDialogAi\AttributeEngine\Util;

/**
 * A composite attribute collection containing other attribute types.
 *
 * createFromInput()
 * @return
 * [
 *  total = IntAttribute,
 *  results = ArrayAttribute
 * ]
 */
class ExampleAbstractAttributeCollection extends AbstractAttributeCollection
{
    const EXAMPLE_TYPE = 'api';
    const EXAMPLE_TYPE_ARRAY = 'array';

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $result = [];
        foreach ($this->getAttributes() as $attribute) {
            $result[] = [
                'id' => $attribute->getID(),
                'type' => $attribute->getType(),
                'value' => $attribute->toString()
            ];
        }
        return Util::encode($result);
    }


    /**
     * @param mixed $input
     * @param string $type
     *
     * @return array
     * [
     *  total = IntAttribute,
     *  results = ArrayAttribute
     * ]
     */
    public function createFromInput($input, $type): array
    {
        $attributes = [];

        if ($type === self::EXAMPLE_TYPE_ARRAY) {
            $attributes[] = new IntAttribute('total', count($input));
            $attributes[] = new ArrayAttribute('results', $input);
        }

        return $attributes;
    }
}
