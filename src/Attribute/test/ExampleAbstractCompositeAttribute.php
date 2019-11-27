<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\Composite\AbstractCompositeAttribute;

class ExampleAbstractCompositeAttribute extends AbstractCompositeAttribute
{
    /**
     * @var string
     */
    protected $attributeCollectionType = ExampleAbstractAttributeCollection::class;
}
