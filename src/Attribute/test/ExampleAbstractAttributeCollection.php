<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\Composite\AbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\IntAttribute;

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
            array_push(
                $result,
                ['id' => $attribute->getID(),
                'type' => $attribute->getType(),
                'value' => $attribute->toString()]
            );
        }
        return json_encode($result);
    }

    /**
     * @inheritDoc
     */
    public function createFromInput($input, $type): array
    {
        $attributes = [];

        if ($type === self::EXAMPLE_TYPE_ARRAY) {
            array_push($attributes, new IntAttribute(
                'total',
                count($input)
            ));
            array_push($attributes, new ArrayAttribute(
                'results',
                $input
            ));
        } elseif ($type === self::EXAMPLE_TYPE) {
            array_push($attributes, new IntAttribute(
                'total',
                count($input)
            ));
            array_push($attributes, new ArrayAttribute(
                'results',
                $input
            ));
            array_push($attributes, new ExampleAbstractCompositeAttribute(
                'case',
                new ExampleAbstractAttributeCollection(
                    [1=>'ok', 2=>'anotherone', 3=>'deeper'],
                    'TEMP_TEST'
                )
            ));
        } else {
            array_push($attributes, new IntAttribute(
                'totaloftotal',
                count($input)
            ));

            array_push($attributes, new ArrayAttribute(
                'resultsofresult',
                $input
            ));
        }

        return $attributes;
    }
}
