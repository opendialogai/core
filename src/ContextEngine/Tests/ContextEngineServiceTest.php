<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\ContextEngine\Contexts\AbstractContext;
use OpenDialogAi\ContextEngine\Contexts\BaseContexts\SessionContext;
use OpenDialogAi\ContextEngine\ContextService\CoreContextService;
use OpenDialogAi\ContextEngine\Contracts\Context;
use OpenDialogAi\ContextEngine\Contracts\ContextService;
use OpenDialogAi\ContextEngine\Facades\ContextService as ContextServiceFacade;
use OpenDialogAi\Core\Tests\TestCase;

class ContextEngineServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function contextService(): ContextService
    {
        return $this->app->make(ContextService::class);
    }

    public function testContextServiceCreation()
    {
        $this->assertInstanceOf(ContextService::class, $this->contextService());
    }

    public function testAddingANewContext()
    {
        $this->createNewContext();
        $this->assertTrue($this->contextService()->hasContext('new_context'));
    }

    public function testAddingAnAttributeToAContext()
    {
        // Create a context and add an attribute to it.
        $newContext = $this->createNewContext();;
        $newContext->addAttribute(new StringAttribute('new_context.test', 'value'));

        // Retrieve the context and retrieve the attribute.
        $newContextA = $this->contextService()->getContext('new_context');
        $attribute = $newContextA->getAttribute('new_context.test');

        $this->assertSame($attribute->getId(), 'new_context.test');
        $this->assertSame($attribute->getValue(), 'value');
    }

    public function testRetrievingAnAttributeDirectly()
    {
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test' => StringAttribute::class]
        );

        // Create a context and add an attribute to it.
        $newContext = $this->createNewContext();;
        $newContext->addAttribute(new StringAttribute('test', 'value'));

        $attribute = $this->contextService()->getAttribute('test', 'new_context');

        $this->assertSame($attribute->getId(), 'test');
        $this->assertSame($attribute->getValue(), 'value');

        // Now try for a context that is not set
        $attribute = $this->contextService()->getAttribute('test', 'new_context1');
        $this->assertEquals('', $attribute->getValue());
    }

    public function testSessionContextCreated()
    {
        $this->assertTrue($this->contextService()->hasContext(CoreContextService::SESSION_CONTEXT));
    }

    public function testSavingUnsupportedAttributeNoContext()
    {
        $this->setConfigValue(
            'opendialog.attribute_engine.custom_attributes',
            ['test_attribute' => StringAttribute::class]
        );

        $attributeName = 'test_attribute';
        $attributeValue = 1;

        $this->contextService()->saveAttribute($attributeName, $attributeValue);

        $attribute = $this->contextService()->getAttribute('test_attribute', 'session');
        $this->assertInstanceOf(StringAttribute::class, $attribute);
        $this->assertSame('1', $attribute->getValue());
    }

    public function testSavingSupportedAttributeUnknownContext()
    {
        $attributeName = 'test_context.test_attribute';
        $attributeValue = 1;

        $this->setCustomAttributes(['test_attribute' => IntAttribute::class]);

        ContextServiceFacade::saveAttribute($attributeName, $attributeValue);

        $attribute = $this->contextService()->getAttribute('test_attribute', 'session');
        $this->assertInstanceOf(IntAttribute::class, $attribute);
        $this->assertSame(1, $attribute->getValue());
    }

    public function testSavingSupportedAttributeKnownContext()
    {
        $attributeName = 'new_context.test_attribute';
        $attributeValue = 1;

        $this->setCustomAttributes(['test_attribute' => IntAttribute::class]);

        $this->createNewContext();
        ContextServiceFacade::saveAttribute($attributeName, $attributeValue);

        $attribute = $this->contextService()->getAttribute('test_attribute', 'new_context');
        $this->assertInstanceOf(IntAttribute::class, $attribute);
        $this->assertSame(1, $attribute->getValue());
    }

    public function testGetNonExistentAttributeValue()
    {
        $this->contextService()->addContext(new SessionContext());
        $value = ContextServiceFacade::getSessionContext()->getAttributeValue('nonexistentvalue');

        $this->assertNull($value);
    }

    public function testGetAttributeValue()
    {
        // Session Context
        ContextServiceFacade::getSessionContext()->addAttribute(new StringAttribute('test', 'test'));
        $this->assertEquals(
            ContextServiceFacade::getSessionContext()->getAttribute('test')->getValue(),
            ContextServiceFacade::getSessionContext()->getAttributeValue('test')
        );
    }

    private function createNewContext(): Context
    {
        $newContext = new class extends AbstractContext {
            protected static string $componentId = 'new_context';
        };

        $this->contextService()->addContext($newContext);

        return $newContext;
    }
}
