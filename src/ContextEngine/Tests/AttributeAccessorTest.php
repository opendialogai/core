<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\AttributeEngine\Attributes\ArrayDataAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Tests\ExampleAbstractAttributeCollection;
use OpenDialogAi\AttributeEngine\Tests\ExampleAbstractCompositeAttribute;
use OpenDialogAi\AttributeEngine\Tests\SecondAbstractAttributeCollection;
use OpenDialogAi\AttributeEngine\Tests\SecondAbstractCompositeAttribute;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineService;

class AttributeAccessorTest extends TestCase
{
    public function testContextServiceMethod()
    {
        $this->addAttributeToSession(new ArrayDataAttribute('test', [1, 2, 3, 4]));

        $value = ContextService::getAttributeValue('test', 'session');


        $this->assertEquals(1, $value[0]);
    }

    public function testAccessingGenericAttribute()
    {
        $this->addAttributeToSession(
            new ArrayDataAttribute(
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

        $arrayValue = ContextService::getAttributeValue('test', 'session');
        $generic = ContextService::getAttributeValue('test', 'session');
        $this->assertEquals([1], $arrayValue['random']);
        $this->assertEquals(1, $arrayValue['random'][0]);
        $this->assertEquals(['another'], $arrayValue[4]['place']);
        $this->assertEquals('piccadilly', $arrayValue['country']['uk']['london']);
    }

    public function testAccessingArrayAttributeDirectly()
    {
        $this->addAttributeToSession(new ArrayDataAttribute('test', [1, 2, 3, 4]));

        $attribute = ContextService::getAttribute('test', 'session');

        $this->assertEquals(1, $attribute->getValue()[0]);
    }

    private function addAttributeToSession($attribute): void
    {
        ContextService::getSessionContext()->addAttribute($attribute);
    }
}
