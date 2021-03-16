<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;


use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Helpers\SerializationTreeHelper;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\TurnCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class TurnNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {
        if(!isset($context[AbstractNormalizer::ATTRIBUTES])) {
            throw new \RuntimeException('The $context["attributes"] value MUST be set when normalizing a conversation object!');
        }
        $tree = $context[AbstractNormalizer::ATTRIBUTES];
        $data = parent::normalize($object, $format, $context);

        if (in_array(Turn::SCENE, array_keys($tree), true)) {
            $data['scene'] = $this->serializer->normalize($object->getScene(), $format,
                SerializationTreeHelper::createChildContext($context, Turn::SCENE));
        }

        if (in_array(Turn::VALID_ORIGINS, array_keys($tree), true)) {
            $data['valid_origins'] = $object->getValidOrigins();
        }

        if (in_array(Turn::REQUEST_INTENTS, array_keys($tree), true)) {
            $data['request_intents'] = $this->serializer->normalize($object->getRequestIntents(), $format,
                SerializationTreeHelper::createChildContext($context, Turn::REQUEST_INTENTS));
        }

        if (in_array(Turn::RESPONSE_INTENTS, array_keys($tree), true)) {
            $data['response_intents'] = $this->serializer->normalize($object->getResponseIntents(), $format,
                SerializationTreeHelper::createChildContext($context, Turn::RESPONSE_INTENTS));
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Turn;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === Turn::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {

        $turn = parent::denormalize($data, $type, $format, $context);

        if (isset($data['scene'])) {
            // Possibility of a circular reference here.
            $scene = $this->serializer->denormalize($data['scene'], Scene::class);

            // If we didn't hydrate $scene->turns, we must manually add the link.
            if($scene->getTurns() === null) {
                $scene->addTurn($turn);
                $turn->setScene($scene);
            } else {
                // We have already loaded the scene
            }
        }

        if(isset($data['request_intents'])) {
            $request_intents = $this->serializer->denormalize($data['request_intents'], IntentCollection::class);
            $turn->setRequestIntents(new IntentCollection());
            foreach($request_intents as $intent) {
                $turn->addRequestIntent($intent);
                $intent->setTurn($turn);
            }
        }

        if(isset($data['response_intents'])) {
            $response_intents = $this->serializer->denormalize($data['response_intents'], IntentCollection::class);
            $turn->setResponseIntents(new IntentCollection());
            foreach($response_intents as $intent) {
                $turn->addResponseIntent($intent);
                $intent->setTurn($turn);
            }
        }

        if(isset($data['valid_origins'])) {
            $turn->setValidOrigins($data['valid_origins']);
        }

        return $turn;

    }
}
