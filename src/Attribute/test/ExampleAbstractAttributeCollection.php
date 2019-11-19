<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\Composite\AbstractAttributeCollection;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
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

        if ($type === self::EXAMPLE_TYPE) {
            // set up
            $results = $input->getResults();
            $total = $input->getNumberOfResults();

            $attributes[] = new IntAttribute('total', $total);
            $attributes[] = new ArrayAttribute('results', $results);
        } elseif ($type === self::EXAMPLE_TYPE_ARRAY) {
            $attributes[] = new IntAttribute('total', count($input));
            $attributes[] = new ArrayAttribute('results', $input);
        }

        return $attributes;
    }
}
