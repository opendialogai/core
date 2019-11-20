<?php

namespace OpenDialogAi\ContextEngine\tests;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractCompositeAttribute;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

class AttributeAccessorTest extends TestCase
{
    public function testContextServiceMethod()
    {
        $this->addAttributeToSession(new ArrayAttribute('test', [1, 2, 3, 4]));

        $value = ContextService::getAttributeValue('session', 'test', [0]);

        $this->assertEquals(1, $value);
    }

    public function testAccessingArrayAttributeDirectly()
    {
        $this->addAttributeToSession(new ArrayAttribute('test', [1, 2, 3, 4]));

        $attribute = ContextService::getAttribute('session', 'test');

        $this->assertEquals(1, $attribute->getValue([0]));
    }

    public function testAccessingArrayAttributeString()
    {
        $this->addAttributeToSession(new ArrayAttribute('test', [1, 2, 3, 4]));

        $response = resolve(ResponseEngineService::class)->fillAttributes('{session.test[0]}');

        $this->assertEquals(1, $response);
    }

    public function testMultiDimensionArrayAttributeString()
    {
        $attribute = new ArrayAttribute('test', [1 => 'hello', 2 => 'goodbye']);
        $this->addAttributeToSession($attribute);

        $response = resolve(ResponseEngineService::class)->fillAttributes('{session.test[2]}');

        $this->assertEquals('goodbye', $response);
    }

    public function testCompositeAttributeString()
    {
        $attribute = new ExampleAbstractCompositeAttribute(
            'test',
            new ExampleAbstractAttributeCollection([1 => 'hello', 2 => 'goodbye'], ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY)
        );
        $this->addAttributeToSession($attribute);

        $response = resolve(ResponseEngineService::class)->fillAttributes('{session.test[total]}');

        $this->assertEquals(2, $response);
    }

    public function testArrayFromCompositeAttributeString()
    {
        $attribute = new ExampleAbstractCompositeAttribute(
            'test',
            new ExampleAbstractAttributeCollection([1 => 'hello', 2 => 'goodbye'], ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY)
        );
        $this->addAttributeToSession($attribute);

        $response = resolve(ResponseEngineService::class)->fillAttributes('{session.test[results][1]}');

        $this->assertEquals('hello', $response);
    }

    private function addAttributeToSession($attribute): void
    {
        ContextService::getSessionContext()->addAttribute($attribute);
    }
}
