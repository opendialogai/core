<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\ContextManager\ContextServiceInterface;
use OpenDialogAi\ContextEngine\Facades\ContextService as ContextServiceFacade;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ContextEngineServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function contextService(): ContextService
    {
        return $this->app->make(ContextServiceInterface::class);
    }

    public function testContextServiceCreation()
    {
        $this->assertInstanceOf(ContextServiceInterface::class, $this->contextService());
    }

    public function testAddingANewContext()
    {
        $this->contextService()->createContext('new_context');
        $this->assertTrue($this->contextService()->hasContext('new_context'));
    }

    public function testAddingAnAttributeToAContext()
    {
        // Create a context and add an attribute to it.
        $newContext = $this->contextService()->createContext('new_context');
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
            'opendialog.context_engine.custom_attributes',
            ['test' => StringAttribute::class]
        );

        // Create a context and add an attribute to it.
        $newContext = $this->contextService()->createContext('new_context');
        $newContext->addAttribute(new StringAttribute('test', 'value'));

        $attribute = $this->contextService()->getAttribute('test', 'new_context');

        $this->assertSame($attribute->getId(), 'test');
        $this->assertSame($attribute->getValue(), 'value');

        // Now try for a context that is not set
        $attribute = $this->contextService()->getAttribute('test', 'new_context1');

        $this->assertSame($attribute->getId(), 'test');
        $this->assertSame($attribute->getValue(), '');
    }

    public function testSessionContextCreated()
    {
        $this->assertTrue($this->contextService()->hasContext(ContextService::SESSION_CONTEXT));
    }

    public function testConversationContextCreated()
    {
        $this->assertTrue($this->contextService()->hasContext(ContextService::CONVERSATION_CONTEXT));
    }

    public function testContextFacade()
    {
        ContextServiceFacade::createContext('test');
        $this->assertTrue(ContextServiceFacade::hasContext('test'));
    }

    public function testSavingUnsupportedAttributeNoContext()
    {
        $this->setConfigValue(
            'opendialog.context_engine.custom_attributes',
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
        $attributeName = 'test_context.test_attribute';
        $attributeValue = 1;

        $this->setCustomAttributes(['test_attribute' => IntAttribute::class]);

        ContextServiceFacade::createContext('test_context');
        ContextServiceFacade::saveAttribute($attributeName, $attributeValue);

        $attribute = $this->contextService()->getAttribute('test_attribute', 'test_context');
        $this->assertInstanceOf(IntAttribute::class, $attribute);
        $this->assertSame(1, $attribute->getValue());
    }

    public function testGetNonExistentAttributeValue()
    {
        ContextServiceFacade::createContext('user');
        $value = ContextServiceFacade::getUserContext()->getAttributeValue('nonexistentvalue');

        $this->assertNull($value);
    }
}
