<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Helpers\SerializationTreeHelper;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\SceneCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ConversationNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        if (!isset($context[AbstractNormalizer::ATTRIBUTES])) {
            throw new \RuntimeException('The $context["attributes"] value MUST be set when normalizing a conversation object!');
        }
        $tree = $context[AbstractNormalizer::ATTRIBUTES];
        $data = parent::normalize($object, $format, $context);

        if (in_array(Conversation::SCENARIO, array_keys($tree), true)) {
            $data['scenario'] = $this->serializer->normalize($object->getScenario(), $format,
                SerializationTreeHelper::createChildContext($context, Conversation::SCENARIO));
        }

        if (in_array(Conversation::SCENES, array_keys($tree), true)) {
            $data['scenes'] = $this->serializer->normalize($object->getScenes(), $format,
                SerializationTreeHelper::createChildContext($context, Conversation::SCENES));
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Conversation;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === Conversation::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $conversation = parent::denormalize($data, $type, $format, $context);

        if (isset($data['scenario'])) {
            // Possibility of a circular reference here.
            $scenario = $this->serializer->denormalize($data['scenario'], Scenario::class);
            // If we didn't hydrate the $scenario->conversations, we must manually add the link.
            if ($scenario->getConversations() === null) {
                $scenario->addConversation($conversation);
                $conversation->setScenario($scenario);
            } else {
                // We have already loaded the conversations.
            }

        }

        if (isset($data['scenes'])) {
            $scenes = $this->serializer->denormalize($data['scenes'], SceneCollection::class);
            $conversation->setScenes(new SceneCollection());
            foreach ($scenes as $scene) {
                // Possible circular reference?
                $conversation->addScene($scene);
                $scene->setConversation($conversation);
            }
        }

        return $conversation;
    }
}
