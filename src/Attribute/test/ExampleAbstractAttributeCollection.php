<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\Composite\AbstractAttributeCollection;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;

class ExampleAbstractAttributeCollection extends AbstractAttributeCollection
{
    const EXAMPLE_TYPE = 'api';

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        // TODO: Implement toString() method.
        $result = [];
        foreach ($this->getAttributes() as $attribute) {
            array_push($result, $attribute->toString());
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
            foreach ($results as $result) {
                array_push(
                    $attributes,
                    new ExampleAbstractCompositeAttribute('custom.attr.id', $result)
                );
            }
        }

        return $attributes;
    }
}
