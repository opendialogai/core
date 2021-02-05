<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeNotRegisteredException;
use OpenDialogAi\AttributeEngine\Exceptions\UnsupportedAttributeTypeException;
use OpenDialogAi\AttributeEngine\DynamicAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class AttributeResolverServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testContextServiceCreation()
    {
        $this->assertTrue($this->getAttributeResolver() instanceof AttributeResolver);
    }

    /**
     * @return mixed
     */
    private function getAttributeResolver(): AttributeResolver
    {
        return $this->app->make(AttributeResolver::class);
    }

    public function testAccessToSupportedAttributes()
    {
        $supportedAttributes = $this->getAttributeResolver()->getSupportedAttributes();

        $this->assertTrue(count($supportedAttributes) > 0);
        $this->assertArrayHasKey('name', $supportedAttributes);
    }

    public function testAccessToDynamicAttributes()
    {
        $dynamicAttribute = DynamicAttribute::create([
            'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => 'attribute.core.int'
        ]);

        $supportedAttributes = $this->getAttributeResolver()->getSupportedAttributes();

        $this->assertArrayHasKey($dynamicAttribute->attribute_id, $supportedAttributes);

        $attribute = $this->getAttributeResolver()->getAttributeFor('test_dynamic_attribute', 1);
        $this->assertInstanceOf(IntAttribute::class, $attribute);
        $this->assertEquals('test_dynamic_attribute', $attribute->getId());
        $this->assertEquals(1, $attribute->getValue());
    }

    public function testAccessToUnsupportedDynamicAttribute()
    {

        DynamicAttribute::create([
            'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => 'attribute.core.int'
        ]);
        DynamicAttribute::truncate();
        $supportedAttributes = $this->getAttributeResolver()->getSupportedAttributes();
        $this->assertArrayNotHasKey('test_dynamic_attribute', $supportedAttributes);
    }

    public function testBindingDynamicAttributesWithUnregisteredCustomType()
    {
        // Don't register any attribute types
        $this->setConfigValue('opendialog.attribute_engine.custom_attribute_types', []);

        $dynamicAttribute = DynamicAttribute::create([
            'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => 'attribute.app.custom'
        ]);

        // Our custom attribute type isn't registered so we should fallback to a string attribute
        Log::spy();
        $this->expectException(AttributeTypeNotRegisteredException::class);
        $attributeResolver = $this->getAttributeResolver();
        Log::shouldHaveReceived('error', [
            sprintf("Not registering dynamic attribute %s - has unknown attribute type identifier %s",
                $dynamicAttribute->attribute_id, $dynamicAttribute->attribute_type)
        ]);
    }

    public function testBindingDynamicAttributesWithRegisteredCustomType()
    {
        // Don't register any attribute types
        $this->setConfigValue('opendialog.attribute_engine.custom_attribute_types',
            [ExampleCustomAttributeType::class]);

        DynamicAttribute::create([
            'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => 'attribute.app.custom'
        ]);

        $attributeResolver = $this->getAttributeResolver();

        $attribute = $attributeResolver->getAttributeFor('test_dynamic_attribute', null);
        $this->assertInstanceOf(ExampleCustomAttributeType::class, $attribute);
        $this->assertEquals('test_dynamic_attribute', $attribute->getId());
    }

    public function testDynamicAttributeNameShadowing()
    {
        // Don't register any attribute types
        $this->setConfigValue('opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => IntAttribute::class]);

        $dynamicAttribute = DynamicAttribute::create([
            'attribute_id' => 'test_attribute', 'attribute_type' => 'attribute.core.string'
        ]);

        \Illuminate\Support\Facades\Log::spy();
        $attributeResolver = $this->getAttributeResolver();
        \Illuminate\Support\Facades\Log::shouldHaveReceived('error',[sprintf(
            "Not registering dynamic attribute %s as it would shadow an existing attribute with the same name.",
            $dynamicAttribute->attribute_id,
        )]);
        $this->assertArrayHasKey('test_attribute', $attributeResolver->getSupportedAttributes());
        $attribute = $this->getAttributeResolver()->getAttributeFor('test_attribute', 1);
        $this->assertInstanceOf(IntAttribute::class, $attribute);

    }

    public function testBadDynamicAttributeBinding()
    {
        $dynamicAttribute = DynamicAttribute::create([
            'attribute_id' => 'test_dynamic_attribute', 'attribute_type' => 'nothing'
        ]);

        $this->expectException(AttributeTypeNotRegisteredException::class);
        Log::spy();
        $attributeResolver = $this->getAttributeResolver();
        Log::shouldHaveReceived('error', [
            sprintf("Not registering dynamic attribute %s - has unknown attribute type identifier %s",
                $dynamicAttribute->attribute_id, $dynamicAttribute->attribute_type)
        ]);
        $this->arrayHasNotKey('test_dynamic_attribute', $attributeResolver->getSupportedAttributes());
    }

    public function testAttributeResolution()
    {
        $attribute = $this->getAttributeResolver()->getAttributeFor('name', 'John Smith');
        $this->assertInstanceOf(StringAttribute::class, $attribute);
        $this->assertEquals($attribute->getValue(), 'John Smith');
        $this->assertNotEquals($attribute->getValue(), 'Mario Rossi');
    }

    public function testAccessToUnsupportedAttribute()
    {
        $attribute = $this->getAttributeResolver()->getAttributeFor('name2', 'John Smith');
        $this->assertEquals(StringAttribute::class, get_class($attribute));
    }

    public function testBindingCustomAttributesWithUnregisteredCustomType()
    {
        // Don't register any attribute types
        $this->setConfigValue('opendialog.attribute_engine.custom_attribute_types', []);

        $this->setConfigValue('opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => ExampleCustomAttributeType::class]);

        $this->expectException(UnsupportedAttributeTypeException::class);

        // Our custom attribute type isn't registered so we expect an unsupported attribute type exception
        $this->getAttributeResolver();
    }

    public function testBindingCustomAttributesWithRegisteredCustomType()
    {
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attribute_types',
            [ExampleCustomAttributeType::class]
        );

        $this->setConfigValue('opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => ExampleCustomAttributeType::class]);

        $attributeResolver = $this->getAttributeResolver();
        $this->assertEquals(ExampleCustomAttributeType::class,
            get_class($attributeResolver->getAttributeFor('test_attribute', null)));
    }

    public function testBadBinding()
    {
        // Bind attribute to non-class
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attribute_types',
            ['nothing']
        );

        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => 'nothing']
        );

        // Registering the attribute type will fail, but NOT fatally so.
        // Registering the attribute should throw an execption because the attribute type is not registered.
        $this->expectException(UnsupportedAttributeTypeException::class);

        Log::spy();
        $this->getAttributeResolver();
        Log::shouldHaveReceived('error', [
            Log::warning(sprintf(
                'Not registering attribute type \'%s\', the attribute type was invalid.',
                'nothing'
            ))
        ]);
        Log::shouldHaveReceived('error',[sprintf(
            "Not registering attribute %s as it has an unknown type %s, please ensure all "
            . "custom attribute types are registered.",
            'test_attribute',
            'nothing'
        )]);

    }
}
