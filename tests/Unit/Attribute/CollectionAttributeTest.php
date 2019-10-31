<?php

namespace OpenDialogAi\Core\Tests\Unit\Attribute;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\CollectionAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

class CollectionAttributeTest extends TestCase
{
    public $setupWithDGraphInit = false;

    public function testCollectionNonArrayValue()
    {
        $value = "string";

        $collection = new CollectionAttribute('test', $value);

        $this->assertIsArray($collection->getValue());
    }

    public function testSetMultiDimensionArray()
    {
        $value = [
            ['name' => '1'],
            ['name' => '2']
        ];

        $collection = new CollectionAttribute('test', $value);

        $this->assertIsArray($collection->getValue());
        $this->assertEquals('1', $collection->getValue()[0]['name']);
        $this->assertEquals('2', $collection->getValue()[1]['name']);
    }

    public function testSetMultiDimensionArrayAttribute()
    {
        $value = [
            ['name' => '1'],
            ['name' => '2']
        ];

        $collection = new ArrayAttribute('test', $value);

        $this->assertIsArray($collection->getValue());
        $this->assertEquals('1', $collection->getValue()[0]->name);
        $this->assertEquals('2', $collection->getValue()[1]->name);
    }

    public function testArrayAttributeResponse()
    {
        $value = [
            ['name' => 'Number 1'],
            ['name' => 'Number 2']
        ];

        $collection = new ArrayAttribute('test', $value);
        ContextService::getSessionContext()->addAttribute($collection);

        $text = resolve(ResponseEngineService::class)->fillAttributes('{session.test[0][name]}');
        $this->assertEquals('Number 1', $text);

        $text = resolve(ResponseEngineService::class)->fillAttributes('{session.test[1][name]}');
        $this->assertEquals('Number 2', $text);
    }
}
