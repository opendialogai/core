<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use Ds\Map;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\AttributeEngineServiceProvider;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
use OpenDialogAi\AttributeEngine\DynamicAttribute;
use OpenDialogAi\AttributeEngine\Exceptions\AttributeTypeNotRegisteredException;
use OpenDialogAi\AttributeEngine\Exceptions\UnsupportedAttributeTypeException;

class AttributeResolverServiceTest extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getPackageProviders($app)
    {
        return [
            AttributeEngineServiceProvider::class,
        ];
    }

    public function setConfigValue($configName, $config)
    {
        $this->app['config']->set($configName, $config);
    }


    public function testAttributeServiceCreation()
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
        // Create a string attribute with a StringAttributeValue
        $attribute = $this->getAttributeResolver()->getAttributeFor('name', new StringAttributeValue('John Smith'));

        $this->assertInstanceOf(StringAttribute::class, $attribute);
        $this->assertEquals($attribute->getValue(), 'John Smith');
        $this->assertEquals($attribute->getAttributeValue()->getTypedValue(), 'John Smith');
        $this->assertNotEquals($attribute->getAttributeValue()->getTypedValue(), 'Mario Rossi');

        // Create a string attribute with a raw value
        $attribute = $this->getAttributeResolver()->getAttributeFor('name', 'Mary Jane');

        $this->assertInstanceOf(StringAttribute::class, $attribute);
        $this->assertEquals($attribute->getValue(), 'Mary Jane');
        $this->assertEquals($attribute->getAttributeValue()->getTypedValue(), 'Mary Jane');
        $this->assertNotEquals($attribute->getAttributeValue()->getTypedValue(), 'Mario Rossi');

        // Create a composite attribute with a new Attribute
        $newAttribute = new StringAttribute('incompo', 'some stuff');
        $attribute = $this->getAttributeResolver()->getAttributeFor('composite', $newAttribute);
        $this->assertInstanceOf(BasicCompositeAttribute::class, $attribute);
        $this->assertEquals($attribute->getAttribute('incompo')->getValue(), 'some stuff');
        $this->assertNotEquals($attribute->getAttribute('incompo')->getValue(), 'other stuff');

        // Create a map and add to composite attribute
        $map = new Map();
        $map->put('forone', new StringAttribute('forone', 'onevalue'));
        $map->put('fortwo', new StringAttribute('fortwo', 'twovalue'));
        $attribute = $this->getAttributeResolver()->getAttributeFor('composite', $map);
        $this->assertInstanceOf(BasicCompositeAttribute::class, $attribute);
        $this->assertEquals(2, count($attribute->getValue()));
        $this->assertEquals($attribute->getAttribute('forone')->getValue(), 'onevalue');
        $this->assertEquals($attribute->getAttribute('fortwo')->getValue(), 'twovalue');
    }

    public function testAccessToUnsupportedAttribute()
    {
        $attribute = $this->getAttributeResolver()->getAttributeFor('name2', null, new StringAttributeValue('John Smith'));
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
        Log::shouldHaveReceived('error', [sprintf(
            "Not registering attribute %s as it has an unknown type %s, please ensure all "
            . "custom attribute types are registered.",
            'test_attribute',
            'nothing'
        )]);
    }

    public function testResolutionThroughCoreCompositeAttribute()
    {
        $resolver = $this->getAttributeResolver();
        /* @var UtteranceAttribute $utteranceAttribute */
        $utteranceAttribute = $resolver->getAttributeFor('utterance');
        $this->assertTrue($utteranceAttribute instanceof UtteranceAttribute);
        $this->assertEquals('utterance', $utteranceAttribute->getId());

        // Add a scalar attribute using an AttributeValue Object
        $utteranceAttribute->setUtteranceAttribute('timestamp', 123456789);
        /* @var IntAttribute $timestamp  */
        $timestamp = $utteranceAttribute->getattribute('timestamp');
        $this->assertEquals(123456789, $timestamp->getValue());

        // Add a composite attribute
        $user = $resolver->getAttributeFor('utterance_user');
        $user->setUserAttribute('first_name', 'mork');

        $utteranceAttribute->setUtteranceAttribute('utterance_user', $user);
        /* @var UserAttribute $retrievedUser */
        $retrieved_user = $utteranceAttribute->getAttribute('utterance_user');
        $this->assertEquals('utterance_user', $retrieved_user->getId());

        /* @var StringAttribute $nameAttribute */
        $nameAttribute = $retrieved_user->getAttribute('first_name');
        $this->assertEquals('mork', $nameAttribute->toString());
    }
}
