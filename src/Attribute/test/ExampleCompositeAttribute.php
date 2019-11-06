<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\Composite\CompositeAttribute;

class ExampleCompositeAttribute extends CompositeAttribute
{
    protected $attributeCollectionType = ExampleAttributeCollection::class;
}
