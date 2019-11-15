<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\Composite\AbstractCompositeAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractCompositeAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class CompositeAttributeTest extends TestCase
{
    public function testCompositeAttribute()
    {
        // createFromInput
        $attributeCollection = new ExampleAbstractAttributeCollection(
            array(['id' => 'one', 'value' => 'go']),
            'array'
        );

        $this->setConfigValue(
            'opendialog.context_engine.custom_attributes',
            [
                'c' => ExampleAbstractCompositeAttribute::class,
                'test_attr' => StringAttribute::class,
                'total' => IntAttribute::class,
                'results' => ArrayAttribute::class
            ]
        );
        $attributeCollectionSerialized = $attributeCollection->jsonSerialize();
        $compositeAttributeFromSerializedCollection = AttributeResolver::getAttributeFor('c', $attributeCollectionSerialized);

        $compositeAttribute = (AttributeResolver::getAttributeFor('c', $attributeCollection));

        $this->assertEquals($compositeAttributeFromSerializedCollection, $compositeAttribute);
        $this->assertEquals($attributeCollection->getAttributes(), $compositeAttribute->getValue());
        $this->assertEquals($compositeAttribute->getType(), AbstractCompositeAttribute::$type);
        $this->assertEquals(get_class($compositeAttribute->getValue()[0]), IntAttribute::class);
        $this->assertEquals(get_class($compositeAttribute->getValue()[1]), ArrayAttribute::class);

        //JSON deserialize
        $attributeCollectionNew = new ExampleAbstractAttributeCollection(
            json_encode(array(['id' => 'test_attr', 'value' => 'go']))
        );
        $compositeAttributeNew = new ExampleAbstractCompositeAttribute(
            'n',
            $attributeCollectionNew
        );

        $this->assertEquals($attributeCollectionNew->jsonSerialize(), '[{"id":"test_attr","value":"go"}]');
    }
}
