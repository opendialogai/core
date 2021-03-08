<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;


use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class TurnNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[AbstractNormalizer::CALLBACKS][Turn::SCENE] = [ConversationObjectNormalizer::class, 'normalizeUidOnly'];
        return parent::normalize($object, $format, $context);

    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Turn;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset($data['type']) && $data['type'] === Turn::TYPE;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $conditions = $this->serializer->denormalize($data['conditions'], ConditionCollection::class);
        $behaviors = $this->serializer->denormalize($data['behaviors'], BehaviorsCollection::class);
        $createdAt = new \DateTime($data['created_at']);
        $updatedAt = new \DateTime($data['updated_at']);
        $requestIntents = $this->serializer->denormalize($data['request_intents'], IntentCollection::class);
        $responseIntents = $this->serializer->denormalize($data['response_intents'], IntentCollection::class);

        $turn = new Turn();
        $turn->setUid($data['uid']);
        $turn->setOdId($data['od_id']);
        $turn->setName($data['name']);
        $turn->setDescription($data['description']);
        $turn->setConditions($conditions);
        $turn->setBehaviors($behaviors);
        $turn->setInterpreter($data['interpreter']);
        $turn->setCreatedAt($createdAt);
        $turn->setUpdatedAt($updatedAt);
        $turn->setValidOrigins($data['valid_origins']);

        foreach($requestIntents as $requestIntent) {
            $turn->addRequestIntent($requestIntent);
            $requestIntent->setTurn($turn);
        }
        foreach($responseIntents as $responseIntent) {
            $turn->addRequestIntent($responseIntent);
            $responseIntent->setTurn($turn);
        }
        return $turn;


    }
}
