<?php

namespace OpenDialogAi\ContextManager\Tests;

use OpenDialogAi\ContextEngine\Exceptions\AttributeCouldNotBeResolvedException;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
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

        $this->assertTrue($attribute instanceof StringAttribute);
        $this->assertTrue($attribute->getValue() == 'John Smith');
        $this->assertFalse($attribute->getValue() == 'Mario Rossi');
    }

    public function testAccessToUnsupportedAttribute()
    {
        $this->expectException(AttributeCouldNotBeResolvedException::class);

        $this->getAttributeResolver()->getAttributeFor('name2', 'John Smith');
    }

    public function testBindingCustomAttributes()
    {
        $this->setConfigValue('opendialog.context_engine.custom_attributes',
            ['test_attribute' => StringAttribute::class]);

        $attributeResolver = $this->getAttributeResolver();
        $this->assertEquals(StringAttribute::class, get_class($attributeResolver->getAttributeFor('test_attribute', null)));
    }

    public function testBadBinding()
    {
        // Bind attribute to non-class
        $this->setConfigValue('opendialog.context_engine.custom_attributes',
            ['test_attribute' => 'nothing']);

        $attributeResolver = $this->getAttributeResolver();

        $this->expectException(AttributeCouldNotBeResolvedException::class);
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
