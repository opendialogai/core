<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\AttributeEngine\Attributes\BasicScalarAttribute;
use OpenDialogAi\AttributeEngine\Contracts\Attribute;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AttributeNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface
{
    protected SerializerInterface $serializer;

    const FIELDS = [
        'id',
        'type',
        'value',
    ];

    /**
     * @param Attribute $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|null
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return [];
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Attribute;
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     * @return Attribute
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): Attribute
    {
        return new BasicScalarAttribute('example');
    }

    /**
     * @param mixed $data
     * @param string $type
     * @param string|null $format
     * @return bool
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === Attribute::class;
    }
}
