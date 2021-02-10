<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use Ds\Map;
use OpenDialogAi\AttributeEngine\AttributeEngineServiceProvider;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\AttributeValues\IntAttributeValue;
use OpenDialogAi\AttributeEngine\AttributeValues\StringAttributeValue;
use OpenDialogAi\AttributeEngine\CoreAttributes\UserAttribute;
use OpenDialogAi\AttributeEngine\CoreAttributes\UtteranceAttribute;
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

    public function testAccessToSupportedAttributes()
    {
        $supportedAttributes = $this->getAttributeResolver()->getSupportedAttributes();

        $this->assertTrue(count($supportedAttributes) > 0);
        $this->assertArrayHasKey('name', $supportedAttributes);
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
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attribute_types',
            []
        );

        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => ExampleCustomAttributeType::class]
        );

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

        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => ExampleCustomAttributeType::class]
        );

        $attributeResolver = $this->getAttributeResolver();
        $this->assertEquals(ExampleCustomAttributeType::class, get_class($attributeResolver->getAttributeFor('test_attribute', null)));
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

        $this->expectException(UnsupportedAttributeTypeException::class);

        // Our custom attribute type isn't valid so we expect an unsupported attribute type exception
        $this->getAttributeResolver();
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

    /**
     * @return mixed
     */
    private function getAttributeResolver(): AttributeResolver
    {
        return $this->app->make(AttributeResolver::class);
    }
}
