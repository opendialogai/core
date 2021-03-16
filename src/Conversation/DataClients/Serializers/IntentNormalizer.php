<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Serializers;

use OpenDialogAi\Core\Conversation\ActionsCollection;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\Helpers\SerializationTreeHelper;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\VirtualIntentCollection;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class IntentNormalizer extends ConversationObjectNormalizer
{
    public function normalize($object, string $format = null, array $context = [])
    {

        if(!isset($context[AbstractNormalizer::ATTRIBUTES])) {
            throw new \RuntimeException('The $context["attributes"] value MUST be set when normalizing a conversation object!');
        }
        $tree = $context[AbstractNormalizer::ATTRIBUTES];
        $data = parent::normalize($object, $format, $context);

        if (in_array(Intent::SPEAKER, $tree, true)) {
            $data['speaker'] = $object->getSpeaker();
        }

        if (in_array(Intent::CONFIDENCE, $tree, true)) {
            $data['confidence'] = $object->getConfidence();
        }

        if(in_array(Intent::SAMPLE_UTTERANCE, $tree, true)) {
            $data['sample_utterance'] = $object->getSampleUtterance();
        }

        if(in_array(Intent::LISTENS_FOR, $tree, true)) {
            $data['listens_for'] = $object->getListensFor();
        }

        if(in_array(Intent::EXPECTED_ATTRIBUTES, $tree, true)) {
            $data['expected_attributes'] = $object->getExpectedAttributes();
        }

        if (in_array(Intent::TURN, array_keys($tree), true)) {
            $data['turn'] = $this->serializer->normalize($object->getTurn(), $format,
                SerializationTreeHelper::createChildContext($context, Intent::TURN));
        }

        if (in_array(Intent::TRANSITION, array_keys($tree), true)) {
            $data['transition'] = $this->serializer->normalize($object->getTransition(), $format,
                SerializationTreeHelper::createChildContext($context, Intent::TRANSITION));
        }

        if(in_array(Intent::VIRTUAL_INTENTS, array_keys($tree), true)) {
            $data['virtual_intents'] = $this->serializer->normalize($object->getVirtualIntents(), $format,
                SerializationTreeHelper::createChildContext($context, Intent::VIRTUAL_INTENTS));
        }

        if(in_array(Intent::ACTIONS, array_keys($tree), true)) {
            $data['actions'] = $this->serializer->normalize($object->getActions(), $format,
                SerializationTreeHelper::createChildContext($context, Intent::ACTIONS));
        }
        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Intent;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $type === Intent::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $intent = parent::denormalize($data, $type, $format, $context);

        if (isset($data['turn'])) {
            // Possibility of a circular reference here.
            $turn = $this->serializer->denormalize($data['turn'], Turn::class);
            $intent->setTurn($turn);
        }

        if(isset($data['speaker'])) {
            $intent->setSpeaker($data['speaker']);
        }

        if(isset($data['confidence'])) {
            $intent->setConfidence($data['confidence']);
        }

        if(isset($data['sample_utterance'])) {
            $intent->setSampleUtterance($data['sample_utterance']);
        }

        if(isset($data['listens_for'])) {
            $intent->setListensFor($data['listens_for']);
        }

        if(isset($data['transition'])) {
            //TODO: Handle transition
            $intent->setTransition(new Transition(null,null,null));
        }

        if(isset($data['virtual_intents'])) {
            // TODO: Handle Virtual Intents
            $virtualIntents = new VirtualIntentCollection();
            $intent->setVirtualIntents($virtualIntents);
        }

        if(isset($data['actions'])) {
            // TODO: Handle actions
            $actions = new ActionsCollection();
            $intent->setActions($actions);
        }

        if(isset($data['expected_attributes'])) {
            $intent->setExpectedAttributes($data['expected_attributes']);
        }

        return $intent;

    }
}
