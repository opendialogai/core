<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use Illuminate\Support\Collection;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class CollectionNormalizer
    implements ContextAwareNormalizerInterface, ContextAwareDenormalizerInterface, SerializerAwareInterface
{
    protected SerializerInterface $serializer;

    public function normalize($object, string $format = null, array $context = [])
    {
        return $object->map(fn($behavior) => $this->serializer->normalize($behavior, $format, $context))->toArray();
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Collection;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}

