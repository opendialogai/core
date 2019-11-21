<?php

namespace OpenDialogAi\Core\Attribute\test;

use OpenDialogAi\Core\Attribute\Composite\AbstractCompositeAttribute;

/**
 * Class SecondAbstractCompositeAttribute
 *
 * @package OpenDialogAi\Core\Attribute\test
 */
class SecondAbstractCompositeAttribute extends AbstractCompositeAttribute
{

    /**
     * @var string
     */
    protected $attributeCollectionType = SecondAbstractAttributeCollection::class;
}
