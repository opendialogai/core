<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Transition;
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

class TransitionNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface
{
    protected SerializerInterface $serializer;


    public function normalize($object, string $format = null, array $context = [])
    {
        return ['conversation' => $object->getConversation(), 'scene' => $object->getScene(), 'turn' => $object->getTurn()];
    }


    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Transition;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return new Transition($data['conversation'] ?? null, $data['scene'] ?? null, $data['turn'] ?? null);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === Transition::class;
    }
}
