<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Exceptions\AttributeIsNotSupported;
use OpenDialogAi\Core\Attribute\StringAttribute;
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
        $this->expectException(AttributeIsNotSupported::class);

        $this->getAttributeResolver()->getAttributeFor('name2', 'John Smith');
    }

    public function testBindingCustomAttributes()
    {
        $this->setConfigValue(
            'opendialog.context_engine.custom_attributes',
            ['test_attribute' => StringAttribute::class]
        );

        $attributeResolver = $this->getAttributeResolver();
        $this->assertEquals(StringAttribute::class, get_class($attributeResolver->getAttributeFor('test_attribute', null)));
    }

    public function testBadBinding()
    {
        // Bind attribute to non-class
        $this->setConfigValue(
            'opendialog.context_engine.custom_attributes',
            ['test_attribute' => 'nothing']
        );

        $attributeResolver = $this->getAttributeResolver();

        $this->expectException(AttributeIsNotSupported::class);
        $attributeResolver->getAttributeFor('test_attribute', null);
    }

    /**
     * @return mixed
     */
    private function getAttributeResolver(): AttributeResolver
    {
        return $this->app->make(AttributeResolver::class);
    }
}
