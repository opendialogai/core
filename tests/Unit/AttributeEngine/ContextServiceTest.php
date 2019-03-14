<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\AttributeEngine\ContextManager\ContextService;
use OpenDialogAi\AttributeEngine\Contexts\CurrentUserContext;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Tests\TestCase;

class ContextServiceTest extends TestCase
{
    public function testContextService()
    {
        $this->assertEquals(config('opendialog.attribute_engine.available_contexts'), $this->app->make(ContextService::CONTEXT_SERVICE)->getAvailableContexts());
    }

    public function testContextRetrieval()
    {
        /* @var  ContextService $contextService */
        $contextService = $this->app->make(ContextService::CONTEXT_SERVICE);

        $context = $contextService->getContextFor('user_context');

        $this->assertTrue($context instanceof CurrentUserContext);
    }
}
