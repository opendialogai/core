<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\Composite\AbstractCompositeAttribute;

class ExampleAbstractCompositeAttribute extends AbstractCompositeAttribute
{
    protected $attributeCollectionType = ExampleAbstractAttributeCollection::class;
}
