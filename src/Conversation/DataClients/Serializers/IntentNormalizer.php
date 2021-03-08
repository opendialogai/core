<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\ActionsCollection;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\VirtualIntentCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class IntentNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[AbstractNormalizer::CALLBACKS][Intent::TURN] = [ConversationObjectNormalizer::class, 'normalizeUidOnly'];

        return parent::normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Intent;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset($data['type']) && $data['type'] === Intent::TYPE;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $conditions = $this->serializer->denormalize($data['conditions'], ConditionCollection::class);
        $behaviors = $this->serializer->denormalize($data['behaviors'], BehaviorsCollection::class);
        $createdAt = new \DateTime($data['created_at']);
        $updatedAt = new \DateTime($data['updated_at']);
        $transition = $this->serializer->denormalize($data['transition'], Transition::class);
        $virtualIntents = $this->serializer->denormalize($data['virtual_intents'], VirtualIntentCollection::class);
        $actions = new ActionsCollection(); /* TODO: Implement this */
        return new Intent($data['uid'], $data['od_id'], $data['name'], $data['description'], $conditions, $behaviors,
            $data['interpreter'], $createdAt, $updatedAt, $data['speaker'], $data['confidence'], $data['sample_utterance'],
            $transition, $data['listens_for'], $virtualIntents, $data['expected_attributes'], $actions);

    }
}
