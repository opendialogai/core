<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\Composite\AbstractCompositeAttribute;

class SecondAbstractCompositeAttribute extends AbstractCompositeAttribute
{
    /**
     * @var string
     */
    protected $attributeCollectionType = SecondAbstractAttributeCollection::class;
}
