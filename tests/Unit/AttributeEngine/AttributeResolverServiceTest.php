<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolverService;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Tests\TestCase;

class AttributeResolverServiceTest extends TestCase
{
    public function testAttributeEngineService()
    {
        $this->assertEquals(config('opendialog.attribute_engine.available_attributes'), $this->app->make(AttributeResolverService::class)->getAvailableAttributes());
    }

    public function testAttributeResolution()
    {
        /* @var  AttributeResolverService $attributeResolver */
        $attributeResolver = $this->app->make(AttributeResolverService::class);

        $attribute = $attributeResolver->getAttributeFor('dummy_attribute');

        $this->assertTrue($attribute instanceof AttributeInterface);
    }
}
