<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\VirtualIntent;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class VirtualIntentNormalizer implements ContextAwareNormalizerInterface, DenormalizerInterface
{

    /**
     * @param  VirtualIntent  $object
     * @param  string|null    $format
     * @param  array          $context
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
