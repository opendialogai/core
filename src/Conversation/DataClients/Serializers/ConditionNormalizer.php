<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Condition;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ConditionNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface
{
    protected SerializerInterface $serializer;

    public function normalize($object, string $format = null, array $context = [])
    {
        /* TODO: Implement Condition Normalization */
        return null;
    }


    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Condition;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return null;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === Condition::class;
    }
}
