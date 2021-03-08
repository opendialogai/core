<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Behavior;
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

class BehaviorNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface
{
    protected SerializerInterface $serializer;

    public function normalize($object, string $format = null, array $context = [])
    {
        return $object->getBehavior();
    }


    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Behavior;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return new Behavior($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === Behavior::class;
    }
}
