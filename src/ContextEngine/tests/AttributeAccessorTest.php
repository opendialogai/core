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

        $value = ContextService::getAttributeValue('test', 'session', [0]);

        $this->assertEquals(1, $value);
    }

    public function testAccessingGenericAttribute()
    {
        $this->addAttributeToSession(
            new ArrayAttribute(
            'test',
            [
                'random' => [1],
                'country' => [
                    3 => "Something",
                    'uk' => [
                        "london" => "piccadilly",
                        ]
                ],
                3 => 'generic',
                4 => [
                    3 => 'something',
                    'place' => ['another']
                ]
            ]
        )
        );

        $arrayValue = ContextService::getAttributeValue('test', 'session', ['random']);
        $specificInsideArray = ContextService::getAttributeValue('test', 'session', ['random', 0]);
        $arrayInsideArray = ContextService::getAttributeValue('test', 'session', [4, 'place']);
        $arrayInsideArrayInsideArray = ContextService::getAttributeValue('test', 'session', [
            'country', 'uk', 'london'
        ]);
        $this->assertEquals([1], $arrayValue);
        $this->assertEquals(1, $specificInsideArray);
        $this->assertEquals(['another'], $arrayInsideArray);
        $this->assertEquals('piccadilly', $arrayInsideArrayInsideArray);
    }

    public function testAccessingArrayAttributeDirectly()
    {
        $this->addAttributeToSession(new ArrayAttribute('test', [1, 2, 3, 4]));

        $attribute = ContextService::getAttribute('test', 'session');

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

    public function testCompositeAttributeFromCompositeAttributeString()
    {
        $attribute = new ExampleAbstractCompositeAttribute(
            'test',
            new ExampleAbstractAttributeCollection([1 => 'hello', 2 => 'goodbye'], ExampleAbstractAttributeCollection::EXAMPLE_TYPE)
        );

        $this->addAttributeToSession($attribute);

        $response = resolve(ResponseEngineService::class)->fillAttributes('{session.test[case][totaloftotal]}');

        $responseArrayValue = resolve(ResponseEngineService::class)->fillAttributes('{session.test[case][resultsofresult][3]}');

        $this->assertEquals(3, $response);
        $this->assertEquals("deeper", $responseArrayValue);
    }

    private function addAttributeToSession($attribute): void
    {
        ContextService::getSessionContext()->addAttribute($attribute);
    }
}
