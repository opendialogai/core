<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\Composite\AbstractCompositeAttribute;

class SecondAbstractCompositeAttribute extends AbstractCompositeAttribute
{
    /**
     * @var string
     */
    protected $attributeCollectionType = SecondAbstractAttributeCollection::class;
}
