<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\SceneCollection;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ConversationNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[AbstractNormalizer::CALLBACKS][Conversation::SCENARIO] = [ConversationObjectNormalizer::class, 'normalizeUidOnly'];
        return parent::normalize($object, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Conversation;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset($data['type']) && $data['type'] === Conversation::TYPE;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $conditions = $this->serializer->denormalize($data['conditions'], ConditionCollection::class);
        $behaviors = $this->serializer->denormalize($data['behaviors'], BehaviorsCollection::class);
        $createdAt = new \DateTime($data['created_at']);
        $updatedAt = new \DateTime($data['updated_at']);
        $scenes = $this->serializer->denormalize($data['scenes'], SceneCollection::class);

        $conversation = new Conversation();
        $conversation->setUid($data['uid']);
        $conversation->setOdId($data['od_id']);
        $conversation->setName($data['name']);
        $conversation->setDescription($data['description']);
        $conversation->setConditions($conditions);
        $conversation->setBehaviors($behaviors);
        $conversation->setInterpreter($data['interpreter']);
        $conversation->setCreatedAt($createdAt);
        $conversation->setUpdatedAt($updatedAt);
        foreach($scenes as $scene) {
            $conversation->addScene($scene);
            $scene->setConversation($conversation);
        }
        return $conversation;
    }
}
