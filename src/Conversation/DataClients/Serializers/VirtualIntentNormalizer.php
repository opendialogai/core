<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\VirtualIntent;
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

class VirtualIntentNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface
{

    /**
     * @param  VirtualIntent $object
     * @param  string|null   $format
     * @param  array         $context
     *
     * @return array
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return ['speaker' => $object->getSpeaker(), 'intentId' => $object->getIntentId()];
    }


    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof VirtualIntent;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return new VirtualIntent($data['speaker'], $data['intentId']);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === VirtualIntent::class;
    }
}
