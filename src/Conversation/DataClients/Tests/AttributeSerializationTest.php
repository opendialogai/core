<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\AttributeEngine\Attributes\BasicCompositeAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\AttributeNormalizer;
use OpenDialogAi\Core\Tests\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

class AttributeSerializationTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testNormalizeScalarAttribute()
    {
        $serializer = new Serializer([new AttributeNormalizer()], [new JsonEncoder()]);
        $attribute = new StringAttribute('my_example_attribute', 'Hello world');

        $data = $serializer->normalize($attribute, 'json', [
            AbstractNormalizer::ATTRIBUTES => AttributeNormalizer::FIELDS
        ]);

        $expected = [
            'id' => 'my_example_attribute',
            'type' => 'attribute.core.string',
            'value' => json_encode([
                'scalar_value' => 'Hello world'
            ])
        ];
        $this->assertEquals($expected, $data);
    }

    public function testDenormalizeScalarAttribute()
    {
        $serializer = new Serializer([new AttributeNormalizer()], [new JsonEncoder()]);
        $data = [
            'id' => 'my_example_attribute',
            'type' => 'attribute.core.string',
            'value' => json_encode([
                'scalar_value' => 'Hello world'
            ])
        ];

        $denormalized = $serializer->denormalize($data, Attribute::class);

        $expected = new StringAttribute('my_example_attribute', 'Hello world');
        $this->assertEquals($expected, $denormalized);
    }
    /**
     * @throws ExceptionInterface
     */
    public function testNormalizeCompositeAttribute()
    {
        $serializer = new Serializer([new AttributeNormalizer()], [new JsonEncoder()]);
        $attribute = new BasicCompositeAttribute('my_example_attribute');
        $attribute->addAttribute(new StringAttribute('my_example_sub_attribute1', 'Hello'));
        $attribute->addAttribute(new StringAttribute('my_example_sub_attribute2', 'World'));

        $data = $serializer->normalize($attribute, 'json', [
            AbstractNormalizer::ATTRIBUTES => AttributeNormalizer::FIELDS
        ]);

        $expected = [
            'id' => 'my_example_attribute',
            'type' => 'attribute.core.composite',
            'value' => json_encode([
                'composite_value' => [
                    [
                        'id' => 'my_example_sub_attribute1',
                        'type' => 'attribute.core.string',
                        'value' => [
                            'scalar_value' => 'Hello'
                        ]
                    ], [
                        'id' => 'my_example_sub_attribute2',
                        'type' => 'attribute.core.string',
                        'value' => [
                            'scalar_value' => 'World'
                        ]
                    ],
                ]
            ])
        ];
        $this->assertEquals($expected, $data);
    }

    public function testDenormalizeCompositeAttribute()
    {
        $serializer = new Serializer([new AttributeNormalizer()], [new JsonEncoder()]);
        $data = [
            'id' => 'my_example_attribute',
            'type' => 'attribute.core.composite',
            'value' => json_encode([
                'composite_value' => [
                    [
                        'id' => 'my_example_sub_attribute1',
                        'type' => 'attribute.core.string',
                        'value' => [
                            'scalar_value' => 'Hello'
                        ]
                    ], [
                        'id' => 'my_example_sub_attribute2',
                        'type' => 'attribute.core.string',
                        'value' => [
                            'scalar_value' => 'World'
                        ]
                    ],
                ]
            ])
        ];

        $denormalized = $serializer->denormalize($data, Attribute::class);

        $expected = new BasicCompositeAttribute('my_example_attribute');
        $expected->addAttribute(new StringAttribute('my_example_sub_attribute1', 'Hello'));
        $expected->addAttribute(new StringAttribute('my_example_sub_attribute2', 'World'));
        $this->assertEquals($expected, $denormalized);
    }
}
