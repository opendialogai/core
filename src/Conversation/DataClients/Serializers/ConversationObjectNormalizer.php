<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class ConversationObjectNormalizer
    implements ContextAwareNormalizerInterface, SerializerAwareInterface, ContextAwareDenormalizerInterface
{
    protected SerializerInterface $serializer;

    public static function normalizeUidOnly($obj)
    {
        return $obj ? $obj->getUid() : null;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $dateCallback = fn($obj) => $obj->format(\DateTime::ISO8601);
        $defaultContext = [
            AbstractNormalizer::CALLBACKS => [
                'createdAt' => $dateCallback, 'updatedAt' => $dateCallback,
            ]
        ];

        $propertyNormalizer = new PropertyNormalizer(null, new
        CamelCaseToSnakeCaseNameConverter(), null, null, null, $defaultContext);
        $propertyNormalizer->setSerializer($this->serializer);
        $data = $propertyNormalizer->normalize($object, $format, $context);
        $data['type'] = $object::TYPE;
        return $data;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
