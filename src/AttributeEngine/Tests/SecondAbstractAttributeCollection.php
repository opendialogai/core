<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\ArrayAttribute;
use OpenDialogAi\AttributeEngine\Attributes\Composite\AbstractAttributeCollection;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Util;

/**
 * A composite attribute collection containing other attribute types.
 * This specific composite attribute will also container another composite attribute.
 *
 * createFromInput()
 * @return
 * [
 *  total = IntAttribute,
 *  results = ArrayAttribute,
 *  test = ExampleAbstractCompositeAttribute
 * ]
 */
class SecondAbstractAttributeCollection extends AbstractAttributeCollection
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
     *  results = ArrayAttribute,
     *  test = ExampleAbstractCompositeAttribute
     * ]
     */
    public function createFromInput($input, $type): array
    {
        $attributes = [];

        if ($type === self::EXAMPLE_TYPE_ARRAY) {
            $attributes[] = new IntAttribute('total', count($input));
            $attributes[] = new ArrayAttribute('results', $input);
            $attributes[] = new ExampleAbstractCompositeAttribute(
                'test',
                new ExampleAbstractAttributeCollection(
                    [1 => 'first', 2 => 'second', 3 => 'third'],
                    ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY
                )
            );
        }

        return $attributes;
    }
}
