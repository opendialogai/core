<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\ConversationCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ScenarioNormalizer extends ConversationObjectNormalizer
{

    public function normalize($object, string $format = null, array $context = [])
    {
        if(!isset($context[AbstractNormalizer::ATTRIBUTES])) {
            throw new \RuntimeException('The $context["attributes"] value MUST be set when normalizing a conversation object!');
        }
        $tree = $context[AbstractNormalizer::ATTRIBUTES];

        $data = parent::normalize($object, $format, $context);

        if (in_array(Scenario::STATUS, $tree, true)) {
            $data['status'] = $object->getStatus();
        }

        if (in_array(Scenario::ACTIVE, $tree, true)) {
            $data['active'] = $object->isActive();
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
        $scenario = parent::denormalize($data, $type, $format, $context);

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
