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


    /**
     * Takes a serialization tree array and filters the top-level
     * based on a an array of allow field names
     *
     * @param  array  $tree
     * @param  array  $allowed
     *
     * @return array
     */
    public static function filterSerializationTree(array $tree, array $allowed): array {
        return array_filter($tree, fn($value, $key) => (is_numeric($key) && in_array($value, $allowed)) || in_array($key,
                $allowed) , ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Creates a 'child' context from a serilization context
     * by descending the serialization tree through the provided attribute.
     *
     * @param  array   $parentContext
     * @param  string  $attribute
     *
     * @return array
     */
    public static function createChildContext(array $parentContext, string $attribute): array
    {
        if (isset($parentContext[AbstractNormalizer::ATTRIBUTES][$attribute])) {
            $parentContext[AbstractNormalizer::ATTRIBUTES] = $parentContext[AbstractNormalizer::ATTRIBUTES][$attribute];
        } else {
            unset($parentContext[AbstractNormalizer::ATTRIBUTES]);
        }

        return $parentContext;
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
