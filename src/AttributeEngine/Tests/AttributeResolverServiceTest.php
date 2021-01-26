<?php

namespace OpenDialogAi\AttributeEngine\Tests;

use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\StringAttribute;
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

    public function testAccessToSupportedAttributes()
    {
        $supportedAttributes = $this->getAttributeResolver()->getSupportedAttributes();

        $this->assertTrue(count($supportedAttributes) > 0);
        $this->assertArrayHasKey('name', $supportedAttributes);
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

    public function testBindingCustomAttributes()
    {
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => StringAttribute::class]
        );

        $attributeResolver = $this->getAttributeResolver();
        $this->assertEquals(StringAttribute::class, get_class($attributeResolver->getAttributeFor('test_attribute', null)));
    }

    public function testBadBinding()
    {
        // Bind attribute to non-class
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => 'nothing']
        );

        $attributeResolver = $this->getAttributeResolver();

        $attribute = $attributeResolver->getAttributeFor('test_attribute', null);
        $this->assertEquals(StringAttribute::class, get_class($attribute));
    }

    /**
     * @return mixed
     */
    private function getAttributeResolver(): AttributeResolver
    {
        return $this->app->make(AttributeResolver::class);
    }
}
