<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ContextEngine\Tests\Contexts\BadlyNamedCustomContext;
use OpenDialogAi\ContextEngine\Tests\Contexts\DummyCustomContext;
use OpenDialogAi\Core\Components\Exceptions\MissingRequiredComponentDataException;
use OpenDialogAi\Core\Tests\TestCase;

class CustomContextsTest extends TestCase
{
    public function testNoCustomContexts()
    {
        // No contexts loaded
        $this->assertCount(0, ContextService::getCustomContexts());
    }

    public function testNonClassContext()
    {
        $this->addCustomContextToConfig('bad');

        // No contexts loaded
        $this->assertCount(0, ContextService::getCustomContexts());
    }

    public function testNoNameContext()
    {
        $this->expectException(MissingRequiredComponentDataException::class);
        $this->addCustomContextToConfig(BadlyNamedCustomContext::class);

        ContextService::getCustomContexts();
    }

    /**
     * @group skip
     */
    public function testValidCustomContext()
    {
        $this->addCustomContextToConfig(DummyCustomContext::class);
        $this->assertCount(1, ContextService::getCustomContexts());
    }

    public function testRemovingContext()
    {
        $context = new DummyCustomContext();

        $context->addAttribute(new StringAttribute('test_string', 'hello_test'));

        $this->assertTrue($context->removeAttribute('test_string'));

        $this->assertNull($context->getAttributeValue('test_string'));
    }

    private function addCustomContextToConfig($customContext)
    {
        $this->setConfigValue('opendialog.context_engine.custom_contexts', [$customContext]);
    }
}
