<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Exceptions\ContextDoesNotExistException;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ContextEngineServiceTest extends TestCase
{
    /** @var ContextService */
    private $contextService;

    public function setUp(): void
    {
        parent::setUp();

        $this->contextService = $this->app->make(ContextService::class);
    }

    public function testContextServiceCreation()
    {
        $this->assertInstanceOf(ContextService::class, $this->contextService);
    }

    public function testAddingANewContext()
    {
        $this->contextService->createContext('new_context');
        $this->assertTrue($this->contextService->hasContext('new_context'));
    }

    public function testAddingAnAttributeToAContext()
    {
        // Create a context and add an attribute to it.
        $newContext = $this->contextService->createContext('new_context');
        $newContext->addAttribute(new StringAttribute('new_context.test', 'value'));

        // Retrieve the context and retrieve the attribute.
        $newContextA = $this->contextService->getContext('new_context');
        $attribute = $newContextA->getAttribute('new_context.test');

        $this->assertSame($attribute->getId(), 'new_context.test');
        $this->assertSame($attribute->getValue(), 'value');
    }

    public function testRetrievingAnAttributeDirectly()
    {
        // Create a context and add an attribute to it.
        $newContext = $this->contextService->createContext('new_context');
        $newContext->addAttribute(new StringAttribute('test', 'value'));

        $attribute = $this->contextService->getAttribute('test', 'new_context');

        $this->assertSame($attribute->getId(), 'test');
        $this->assertSame($attribute->getValue(), 'value');

        $this->expectException(ContextDoesNotExistException::class);
        $this->expectExceptionMessage('Context new_context1 for attribute test not available.');

        // Now try for a context that is not set
        $this->contextService->getAttribute('test', 'new_context1');
    }

    public function testSessionContextCreated()
    {
        $this->assertTrue($this->contextService->hasContext(ContextService::SESSION_CONTEXT));
    }
}
