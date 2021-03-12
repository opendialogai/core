<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Helpers\SerializationTreeHelper;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\SceneCollection;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SceneNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        if(!isset($context[AbstractNormalizer::ATTRIBUTES])) {
            throw new \RuntimeException('The $context["attributes"] value MUST be set when normalizing a conversation object!');
        }
        $tree = $context[AbstractNormalizer::ATTRIBUTES];
        $data = parent::normalize($object, $format, $context);

        if (in_array(Scene::CONVERSATION, array_keys($tree), true)) {
            $data['conversation'] = $this->serializer->normalize($object->getConversation(), $format,
                SerializationTreeHelper::createChildContext($context, Scene::CONVERSATION));
        }

        if (in_array(Scene::TURNS, array_keys($tree), true)) {
            $data['turns'] = $this->serializer->normalize($object->getTurns(), $format,
                SerializationTreeHelper::createChildContext($context, Scene::TURNS));
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Scene;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === Scene::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $scene = parent::denormalize($data, $type, $format, $context);

        if (isset($data['conversation'])) {
            // Possibility of a circular reference here.
            $conversation = $this->serializer->denormalize($data['conversation'], Conversation::class);

            // If we didn't hydrate $conversation->scenes, we must manually add the link.
            if($conversation->getScenes() === null) {
                $conversation->addScene($scene);
                $scene->setConversation($conversation);
            } else {
                // We have already loaded the conversations.
            }
        }

        if(isset($data['turns'])) {
            $turns = $this->serializer->denormalize($data['turns'], TurnCollection::class);
            $scene->setTurns(new TurnCollection());
            foreach ($turns as $turn) {
                $scene->addTurn($turn);
                $turn->setScene($scene);
            }
        }

        return $scene;

    }
}
