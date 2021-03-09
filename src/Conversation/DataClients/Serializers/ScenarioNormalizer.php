<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ScenarioNormalizer extends ConversationObjectNormalizer
{

    const FULL_EXPANSION = [
        Scenario::UID,
        Scenario::OD_ID,
        Scenario::NAME,
        Scenario::DESCRIPTION,
        Scenario::BEHAVIORS => Behavior::FIELDS,
        Scenario::CONDITIONS => Condition::FIELDS,
        Scenario::INTERPRETER,
        Scenario::UPDATED_AT,
        Scenario::CREATED_AT,
        Scenario::ACTIVE,
        Scenario::STATUS,
        // TODO: Reintroduce
        // Scenario::CONVERSATIONS => Conversation::FULL_EXPANSION
    ];


    public function normalize($object, string $format = null, array $context = [])
    {
        $tree = $context[AbstractNormalizer::ATTRIBUTES] ?? self::FULL_EXPANSION;

        $data = [];
        if (in_array(Scenario::UID, $tree)) {
            $data['id'] = $object->getUid();
        }
        if (in_array(Scenario::OD_ID, $tree)) {
            $data['od_id'] = $object->getOdId();
        }
        if (in_array(Scenario::NAME, $tree)) {
            $data['name'] = $object->getName();
        }

        if (in_array(Scenario::DESCRIPTION, $tree)) {
            $data['description'] = $object->getDescription();
        }

        if (in_array(Scenario::INTERPRETER, $tree)) {
            $data['interpreter'] = $object->getInterpreter();
        }

        if (in_array(Scenario::STATUS, $tree)) {
            $data['status'] = $object->getStatus();
        }

        if (in_array(Scenario::ACTIVE, $tree)) {
            $data['active'] = $object->isActive();
        }

        if (in_array(Scenario::CREATED_AT, $tree)) {
            $data['created_at'] = $object->getCreatedAt()->format(\DateTime::ISO8601);
        }
        if (in_array(Scenario::UPDATED_AT, $tree)) {
            $data['updated_at'] = $object->getUpdatedAt()->format(\DateTime::ISO8601);
        }

        if (in_array(Scenario::CONDITIONS, array_keys($tree), true)) {
            $data['conditions'] = $this->serializer->normalize($object->getConditions(), $format,
                $this->createChildContext($context, Scenario::CONDITIONS));
        }

        if (in_array(Scenario::BEHAVIORS, array_keys($tree), true)) {
            $data['behaviors'] = $this->serializer->normalize($object->getBehaviors(), $format,
                $this->createChildContext($context, Scenario::BEHAVIORS));
        }

        if (in_array(Scenario::CONVERSATIONS, $tree, true)) {
            $data['conversations'] = $this->serializer->normalize($object->getConversations(), $format,
                $this->createChildContext($context, Scenario::CONVERSATIONS));
        }
        if (in_array(Scenario::CONVERSATIONS, array_keys($tree), true)) {
            $data['conversations'] = $this->serializer->normalize($object->getConversations(), $format,
                $this->createChildContext($context, Scenario::CONVERSATIONS));
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Scenario;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === Scenario::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $scenario = new Scenario();
        if (isset($data['id'])) {
            $scenario->setUid($data['id']);
        }

        if (isset($data['od_id'])) {
            $scenario->setOdId($data['od_id']);
        }

        if (isset($data['name'])) {
            $scenario->setName($data['name']);
        }

        if (isset($data['description'])) {
            $scenario->setDescription($data['description']);
        }

        if (isset($data['conditions'])) {
            //TODO: Reinclude conditions
            //            $conditions = $this->serializer->denormalize($data['conditions'], ConditionCollection::class);
            //            $scenario->setConditions($conditions);
        }

        if (isset($data['behaviors'])) {
            $behaviors = $this->serializer->denormalize($data['behaviors'], BehaviorsCollection::class);
            $scenario->setBehaviors($behaviors);
        }

        if (isset($data['interpreter'])) {
            $scenario->setInterpreter($data['interpreter']);
        }

        if (isset($data['created_at'])) {
            $createdAt = new \DateTime($data['created_at']);
            $scenario->setCreatedAt($createdAt);
        }

        if (isset($data['updated_at'])) {
            $updatedAt = new \DateTime($data['updated_at']);
            $scenario->setUpdatedAt($updatedAt);
        }

        if (isset($data['active'])) {
            $scenario->setActive($data['active']);
        }

        if (isset($data['status'])) {
            $scenario->setStatus($data['status']);
        }

        if (isset($data['conversations'])) {
            $conversations = $this->serializer->denormalize($data['conversations'], ConversationCollection::class);
            foreach ($conversations as $conversation) {
                $scenario->addConversation($conversation);
                $conversation->setScenario($scenario);
            }
        }

        return $scenario;

    }
}
