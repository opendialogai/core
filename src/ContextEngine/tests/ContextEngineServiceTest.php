<?php

namespace OpenDialogAi\ContextManager\Tests;

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

        $this->contextService = $this->app->make(ContextService::CONTEXT_SERVICE);
    }

    public function testContextServiceCreation()
    {
        $this->assertTrue($this->contextService instanceof ContextService);
    }

    public function testAddingANewContext()
    {
        $newContext = $this->contextService->createContext('context.core.new_context');
        $this->assertTrue($this->contextService->hasContext('context.core.new_context'));
    }

    public function testAddingAnAttributeToAContext()
    {
        // Create a context and add an attribute to it.
        $newContext = $this->contextService->createContext('context.core.new_context');
        $newContext->addAttribute(new StringAttribute('test', 'value'));

        // Retrieve the context and retrieve the attribute.
        $newContextA = $this->contextService->getContext('context.core.new_context');
        $attribute = $newContextA->getAttribute('test');

        $this->assertTrue($attribute->getId() == 'test');
        $this->assertTrue($attribute->getValue() == 'value');
    }
}
