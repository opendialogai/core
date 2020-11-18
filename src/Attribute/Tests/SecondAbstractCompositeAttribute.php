<?php

namespace OpenDialogAi\Core\Attribute\Tests;

use OpenDialogAi\Core\Attribute\Composite\AbstractCompositeAttribute;

class SecondAbstractCompositeAttribute extends AbstractCompositeAttribute
{
    /**
     * @var string
     */
    protected $attributeCollectionType = SecondAbstractAttributeCollection::class;
}
