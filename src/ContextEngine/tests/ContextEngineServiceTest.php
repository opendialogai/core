<?php

namespace OpenDialogAi\ContextManager\Tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextDoesNotExistException;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ContextEngineServiceTest extends TestCase
{
    /** @var ContextService */
    private $contextService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->contextService = $this->app->make(ContextService::class);
    }

    public function testContextServiceCreation()
    {
        $this->assertTrue($this->contextService instanceof ContextService);
    }

    public function testAddingANewContext()
    {
        $newContext = $this->contextService->createContext('new_context');
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

        $this->assertTrue($attribute->getId() == 'new_context.test');
        $this->assertTrue($attribute->getValue() == 'value');
    }

    public function testRetrievingAnAttributeDirectly()
    {
        // Create a context and add an attribute to it.
        $newContext = $this->contextService->createContext('new_context');
        $newContext->addAttribute(new StringAttribute('test', 'value'));

        $attribute = $this->contextService->getAttribute('test', 'new_context');

        $this->assertTrue($attribute->getId() == 'test');
        $this->assertTrue($attribute->getValue() == 'value');

        $this->expectException(ContextDoesNotExistException::class);
        $this->expectExceptionMessage('Context new_context1 for attribute test not available.');
        // Now try for a context that is not set
        $attribute = $this->contextService->getAttribute('test', 'new_context1');

    }
}
