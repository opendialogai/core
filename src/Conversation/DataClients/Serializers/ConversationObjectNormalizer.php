<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class ConversationObjectNormalizer
    implements ContextAwareNormalizerInterface, SerializerAwareInterface, ContextAwareDenormalizerInterface
{

    public static function fullExpansion() {
        return [
            ConversationObject::UID,
            ConversationObject::OD_ID,
            ConversationObject::NAME,
            ConversationObject::DESCRIPTION,
            ConversationObject::BEHAVIORS => BehaviorNormalizer::FULL_EXPANSION,
            ConversationObject::CONDITIONS => ConditionNormalizer::FULL_EXPANSION,
            ConversationObject::INTERPRETER,
            ConversationObject::CREATED_AT,
            ConversationObject::UPDATED_AT
        ];
    }
    protected SerializerInterface $serializer;

    public static function normalizeUidOnly($obj)
    {
        return $obj ? $obj->getUid() : null;
    }


    /**
     * Takes a serialization tree array and filters the top-level
     * based on a an array of allow field names
     *
     * @param  array  $tree
     * @param  array  $allowed
     *
     * @return array
     */
    public static function filterSerializationTree(array $tree, array $allowed): array
    {
        return array_filter($tree,
            fn($value, $key) => (is_numeric($key) && in_array($value, $allowed, true)) || in_array($key, $allowed, true),
            ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Creates a 'child' context from a serilization context
     * by descending the serialization tree through the provided attribute.
     *
     * @param  array   $parentContext
     * @param  string  $attribute
     *
     * @return array
     */
    public static function createChildContext(array $parentContext, string $attribute): array
    {
        if (isset($parentContext[AbstractNormalizer::ATTRIBUTES][$attribute])) {
            $parentContext[AbstractNormalizer::ATTRIBUTES] = $parentContext[AbstractNormalizer::ATTRIBUTES][$attribute];
        } else {
            unset($parentContext[AbstractNormalizer::ATTRIBUTES]);
        }

        return $parentContext;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $tree = $context[AbstractNormalizer::ATTRIBUTES] ?? self::fullExpansion();

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


        return $data;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $object = new $type();
        if (isset($data['id'])) {
            $object->setUid($data['id']);
        }

        if (isset($data['od_id'])) {
            $object->setOdId($data['od_id']);
        }

        if (isset($data['name'])) {
            $object->setName($data['name']);
        }

        if (isset($data['description'])) {
            $object->setDescription($data['description']);
        }

        if (isset($data['conditions'])) {
            //TODO: Reinclude conditions
            //            $conditions = $this->serializer->denormalize($data['conditions'], ConditionCollection::class);
            //            $object->setConditions($conditions);
            // Use empty collection for now so we can still fully normalize/denormalize
            $object->setConditions(new ConditionCollection());
        }

        if (isset($data['behaviors'])) {
            $behaviors = $this->serializer->denormalize($data['behaviors'], BehaviorsCollection::class);
            $object->setBehaviors($behaviors);
        }

        if (isset($data['interpreter'])) {
            $object->setInterpreter($data['interpreter']);
        }

        if (isset($data['created_at'])) {
            $createdAt = new \DateTime($data['created_at']);
            $object->setCreatedAt($createdAt);
        }

        if (isset($data['updated_at'])) {
            $updatedAt = new \DateTime($data['updated_at']);
            $object->setUpdatedAt($updatedAt);
        }
        return $object;

    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
