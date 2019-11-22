<?php

namespace OpenDialogAi\ContextEngine\tests;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\ArrayAttribute;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\test\ExampleAbstractCompositeAttribute;
use OpenDialogAi\Core\Attribute\test\SecondAbstractAttributeCollection;
use OpenDialogAi\Core\Attribute\test\SecondAbstractCompositeAttribute;
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
                3 => [
                    [1],
                    [2]
                ],
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
        $generic = ContextService::getAttributeValue('test', 'session', [3, 1, 0]);
        $this->assertEquals([1], $arrayValue);
        $this->assertEquals(1, $specificInsideArray);
        $this->assertEquals(['another'], $arrayInsideArray);
        $this->assertEquals('piccadilly', $arrayInsideArrayInsideArray);
        $this->assertEquals(2, $generic);
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
        $testData = [1 => 'hello', 2 => 'goodbye'];
        $attribute = new ExampleAbstractCompositeAttribute(
            'test',
            new ExampleAbstractAttributeCollection($testData, ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY)
        );
        $this->addAttributeToSession($attribute);

        $response = resolve(ResponseEngineService::class)->fillAttributes('{session.test[total]}');
        $attributeInt = ContextService::getAttributeValue("test", "session", ["total"]);
        $attributeArray = ContextService::getAttributeValue("test", "session", ["results"]);
        $attributeArrayFirstValue = ContextService::getAttributeValue("test", "session", ["results", 1]);


        $this->assertEquals(2, $response);
        $this->assertEquals($testData, $attributeArray->getValue());
        $this->assertEquals(count($testData), $attributeInt->getValue());
        $this->assertEquals($testData[1], $attributeArrayFirstValue);
    }

    public function testArrayFromCompositeAttributeString()
    {
        $attribute = new ExampleAbstractCompositeAttribute(
            'test',
            new ExampleAbstractAttributeCollection([1 => 'hello', 2 => 'goodbye'], ExampleAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY)
        );
        $this->addAttributeToSession($attribute);

        ContextService::getAttributeValue('session', 'test', ['results']);

        $response = resolve(ResponseEngineService::class)->fillAttributes('{session.test[results][1]}');

        $this->assertEquals('hello', $response);
    }

    public function testCompositeAttributeFromCompositeAttributeString()
    {
        $attribute = new SecondAbstractCompositeAttribute(
            'second',
            new SecondAbstractAttributeCollection(
                [1 => 'hello', 2 => 'goodbye'],
                SecondAbstractAttributeCollection::EXAMPLE_TYPE_ARRAY
            )
        );

        $this->addAttributeToSession($attribute);

        $response = resolve(ResponseEngineService::class)
            ->fillAttributes('{session.second[test][total]}');
        $responseArrayValue = resolve(ResponseEngineService::class)
            ->fillAttributes('{session.second[test][results][3]}');

        $this->assertEquals(3, $response);
        $this->assertEquals("third", $responseArrayValue);
    }

    private function addAttributeToSession($attribute): void
    {
        ContextService::getSessionContext()->addAttribute($attribute);
    }
}
