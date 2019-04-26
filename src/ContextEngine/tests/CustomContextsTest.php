<?php

namespace OpenDialogAi\ContextEngine\tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\tests\contexts\BadlyNamedCustomContext;
use OpenDialogAi\ContextEngine\tests\contexts\DummyCustomContext;
use OpenDialogAi\Core\Tests\TestCase;

class CustomContextsTest extends TestCase
{
    public function testNoCustomContexts()
    {
        /** @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);

        // No contexts loaded
        $this->assertCount(0, $contextService->getContexts());
    }

    public function testNonClassContext()
    {
        $this->addCustomContextToConfig('bad');

        /** @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);

        // No contexts loaded
        $this->assertCount(0, $contextService->getContexts());
    }

    public function testNoNameContext()
    {
        $this->addCustomContextToConfig(BadlyNamedCustomContext::class);

        /** @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);

        // No contexts loaded
        $this->assertCount(0, $contextService->getContexts());
    }

    public function testValidCustomContext()
    {
        $this->addCustomContextToConfig(DummyCustomContext::class);

        /** @var ContextService $contextService */
        $contextService = $this->app->make(ContextService::class);

        $this->assertCount(1, $contextService->getContexts());

        $context = $contextService->getContext(DummyCustomContext::$name);
        $this->assertCount(3, $context->getAttributes());

        $value = $contextService->getAttributeValue('1', DummyCustomContext::$name);
        $this->assertEquals(1, $value);
    }

    private function addCustomContextToConfig($customContext)
    {
        $this->setConfigValue('opendialog.context_engine.custom_contexts', [$customContext]);
    }
}
