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

        $data =  parent::normalize($object, $format, $context);
        unset($data['interpreted_intents']);
        unset($data['attributes']);
        return $data;
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
        $intent = new Intent();
        $intent->setUid($data['uid']);
        $intent->setOdId($data['od_id']);
        $intent->setName($data['name']);
        $intent->setDescription($data['description']);
        $intent->setConditions($conditions);
        $intent->setBehaviors($behaviors);
        $intent->setInterpreter($data['interpreter']);
        $intent->setCreatedAt($createdAt);
        $intent->setUpdatedAt($updatedAt);
        $intent->setSpeaker($data['speaker']);
        $intent->setConfidence($data['confidence']);
        $intent->setSampleUtterance($data['sample_utterance']);
        $intent->setTransition($transition);
        $intent->setListensFor($data['listens_for']);
        $intent->setVirtualIntents($virtualIntents);
        $intent->setExpectedAttributes($data['expected_attributes']);
        $intent->setActions($actions);
        return $intent;

    }
}
