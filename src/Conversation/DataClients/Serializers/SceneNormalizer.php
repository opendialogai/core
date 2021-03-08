<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SceneNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[AbstractNormalizer::CALLBACKS][Scene::CONVERSATION] = [ConversationObjectNormalizer::class, 'normalizeUidOnly'];
        return parent::normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Scene;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset($data['type']) && $data['type'] === Scene::TYPE;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $conditions = $this->serializer->denormalize($data['conditions'], ConditionCollection::class);
        $behaviors = $this->serializer->denormalize($data['behaviors'], BehaviorsCollection::class);
        $createdAt = new \DateTime($data['created_at']);
        $updatedAt = new \DateTime($data['updated_at']);
        $turns = $this->serializer->denormalize($data['turns'], TurnCollection::class);

        $scene = new Scene();
        $scene->setUid($data['uid']);
        $scene->setOdId($data['od_id']);
        $scene->setName($data['name']);
        $scene->setDescription($data['description']);
        $scene->setConditions($conditions);
        $scene->setBehaviors($behaviors);
        $scene->setInterpreter($data['interpreter']);
        $scene->setCreatedAt($createdAt);
        $scene->setUpdatedAt($updatedAt);
        foreach ($turns as $turn) {
            $scene->addTurn($turn);
            $turn->setScene($scene);
        }
        return $scene;

    }
}
