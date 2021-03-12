<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use http\Exception\RuntimeException;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Helpers\SerializationTreeHelper;
use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class ConversationObjectNormalizer
    implements ContextAwareNormalizerInterface, SerializerAwareInterface, ContextAwareDenormalizerInterface
{
    protected SerializerInterface $serializer;


    public function normalize($object, string $format = null, array $context = [])
    {
        if(!isset($context[AbstractNormalizer::ATTRIBUTES])) {
            throw new \RuntimeException('The $context["attributes"] value MUST be set when normalizing a conversation object!');
        }
        $tree = $context[AbstractNormalizer::ATTRIBUTES];

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
                SerializationTreeHelper::createChildContext($context, Scenario::CONDITIONS));
        }

        if (in_array(Scenario::BEHAVIORS, array_keys($tree), true)) {
            $data['behaviors'] = $this->serializer->normalize($object->getBehaviors(), $format,
                SerializationTreeHelper::createChildContext($context, Scenario::BEHAVIORS));
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
