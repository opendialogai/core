<?php


use Mockery\MockInterface;
use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Attributes\BooleanAttribute;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Contracts\CompositeAttribute;
use OpenDialogAi\ContextEngine\DataClients\GraphAttributeDataClient;
use OpenDialogAi\ContextEngine\Exceptions\CouldNotLoadAttributeException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\GraphQLClient\GraphQLClientInterface;

class GraphAttributeDataClientTest extends TestCase
{
    /**
     * @throws CouldNotLoadAttributeException
     */
    public function testLoadAttributesSuccessfulWithAttributes()
    {
        $this->partialMock(GraphQLClientInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn([
                    'data' => [
                        'getUser' => [
                            'contexts' => [
                                [
                                    'attributes' => [
                                        [
                                            'id' => 'attribute1',
                                            'type' => 'attribute.core.string',
                                            'value' => json_encode(['scalar_value' => 'Hello'])
                                        ], [
                                            'id' => 'attribute2',
                                            'type' => 'attribute.core.string',
                                            'value' => json_encode(['scalar_value' => 'World'])
                                        ], [
                                            'id' => 'attribute3',
                                            'type' => 'attribute.core.composite',
                                            'value' => json_encode(['composite_value' => [
                                                [
                                                    'id' => 'attribute4',
                                                    'type' => 'attribute.core.int',
                                                    'value' => ['scalar_value' => '2']
                                                ], [
                                                    'id' => 'attribute5',
                                                    'type' => 'attribute.core.boolean',
                                                    'value' => ['scalar_value' => 'false']
                                                ],
                                            ]])
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
        });

        $dataClient = resolve(GraphAttributeDataClient::class);
        $attributes = $dataClient->loadAttributes('my_context', 'user123');

        $this->assertCount(3, $attributes->getAttributes());
        $this->assertTrue($attributes->hasAttribute('attribute1'));
        $this->assertTrue($attributes->hasAttribute('attribute2'));
        $this->assertInstanceOf(StringAttribute::class, $attributes->getAttribute('attribute1'));
        $this->assertInstanceOf(StringAttribute::class, $attributes->getAttribute('attribute2'));
        $this->assertEquals('Hello', $attributes->getAttributeValue('attribute1'));
        $this->assertEquals('World', $attributes->getAttributeValue('attribute2'));

        $this->assertTrue($attributes->hasAttribute('attribute3'));

        /** @var CompositeAttribute $compositeAttribute */
        $compositeAttribute = $attributes->getAttribute('attribute3');

        $this->assertInstanceOf(BasicCompositeAttribute::class, $compositeAttribute);
        $this->assertCount(2, $compositeAttribute->getAttributes());
        $this->assertInstanceOf(IntAttribute::class, $compositeAttribute->getAttribute('attribute4'));
        $this->assertInstanceOf(BooleanAttribute::class, $compositeAttribute->getAttribute('attribute5'));
        $this->assertEquals(2, $compositeAttribute->getAttribute('attribute4')->getValue());
        $this->assertEquals(false, $compositeAttribute->getAttribute('attribute5')->getValue());
    }

    /**
     * @throws CouldNotLoadAttributeException
     */
    public function testLoadAttributesSuccessfulWithoutAttributes()
    {
        $this->partialMock(GraphQLClientInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn([
                    'data' => [
                        'getUser' => [
                            'contexts' => []
                        ]
                    ]
                ]);
        });

        $dataClient = resolve(GraphAttributeDataClient::class);
        $attributes = $dataClient->loadAttributes('my_context', 'user123');

        $this->assertCount(0, $attributes->getAttributes());
    }

    public function testLoadAttributesFailure()
    {
        $this->partialMock(GraphQLClientInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn([
                    'data' => [
                        'getUser' => []
                    ]
                ]);
        });

        $this->expectException(CouldNotLoadAttributeException::class);

        $dataClient = resolve(GraphAttributeDataClient::class);
        $dataClient->loadAttributes('my_context', 'user123');
    }

    /**
     * @throws CouldNotLoadAttributeException
     */
    public function testLoadAttributeSuccessfulWithAttribute()
    {
        $this->partialMock(GraphQLClientInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn([
                    'data' => [
                        'getUser' => [
                            'contexts' => [
                                [
                                    'attributes' => [
                                        [
                                            'id' => 'attribute1',
                                            'type' => 'attribute.core.string',
                                            'value' => json_encode(['scalar_value' => 'Hello'])
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
        });

        $dataClient = resolve(GraphAttributeDataClient::class);
        $attribute = $dataClient->loadAttribute('my_context', 'user123', 'attribute1');

        $this->assertEquals('attribute1', $attribute->getId());
        $this->assertInstanceOf(StringAttribute::class, $attribute);
        $this->assertEquals('Hello', $attribute->getValue());
    }

    /**
     * @throws CouldNotLoadAttributeException
     */
    public function testLoadAttributeSuccessfulWithoutAttribute()
    {
        $this->partialMock(GraphQLClientInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn([
                    'data' => [
                        'getUser' => [
                            'contexts' => []
                        ]
                    ]
                ]);
        });

        $dataClient = resolve(GraphAttributeDataClient::class);
        $attribute = $dataClient->loadAttribute('my_context', 'user123', 'attribute1');

        $this->assertNull($attribute);
    }

    /**
     * @throws CouldNotLoadAttributeException
     */
    public function testLoadAttributeFailure()
    {
        $this->partialMock(GraphQLClientInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('query')
                ->once()
                ->andReturn([
                    'data' => [
                        'getUser' => []
                    ]
                ]);
        });

        $this->expectException(CouldNotLoadAttributeException::class);

        $dataClient = resolve(GraphAttributeDataClient::class);
        $dataClient->loadAttribute('my_context', 'user123', 'attribute1');
    }
}
