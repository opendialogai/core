<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\SceneCollection;
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
        $conversations = $this->serializer->denormalize($data['conversations'], ConversationCollection::class);

        $scenario = new Scenario();
        $scenario->setUid($data['uid']);
        $scenario->setOdId($data['od_id']);
        $scenario->setName($data['name']);
        $scenario->setDescription($data['description']);
        $scenario->setConditions($conditions);
        $scenario->setBehaviors($behaviors);
        $scenario->setInterpreter($data['interpreter']);
        $scenario->setCreatedAt($createdAt);
        $scenario->setUpdatedAt($updatedAt);
        $scenario->setActive($data['active']);
        $scenario->setStatus($data['status']);
        foreach($conversations as $conversation) {
            $scenario->addConversation($conversation);
            $conversation->setScenario($scenario);
        }
        return $scenario;

    }
}
