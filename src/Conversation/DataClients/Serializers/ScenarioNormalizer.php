<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ScenarioNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[AbstractNormalizer::CALLBACKS]['scenario'] = [ConversationObjectNormalizer::class, 'normalizeUidOnly'];
        return parent::normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Scenario;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset($data['type']) && $data['type'] === Scenario::TYPE;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $conditions = $this->serializer->denormalize($data['conditions'], ConditionCollection::class);
        $behaviors = $this->serializer->denormalize($data['behaviors'], BehaviorsCollection::class);
        $createdAt = new \DateTime($data['created_at']);
        $updatedAt = new \DateTime($data['updated_at']);
        $scenario = new Scenario($data['uid'], $data['od_id'], $data['name'], $data['description'], $conditions, $behaviors,
            $data['interpreter'], $createdAt, $updatedAt, $data['active'], $data['status']);
        $conversations = $this->serializer->denormalize($data['conversations'], ConversationCollection::class);
        foreach($conversations as $conversation) {
            $scenario->addConversation($conversation);
            $conversation->setScenario($scenario);
        }

        return $scenario;

    }
}
