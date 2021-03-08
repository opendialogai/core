<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TransitionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentNormalizer;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;

class IntentSerializationTest extends SerializationTestCase
{
    public function testNormalizeStandaloneIntent()
    {
        $intent = $this->getStandaloneIntent();
        $normalizers = [new IntentNormalizer(), new TransitionNormalizer(), new VirtualIntentNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($intent, 'json', []);
        $expected = [
            'type' => Intent::TYPE,
            'uid' => $intent->getUid(),
            'od_id' => $intent->getOdId(),
            'name' => $intent->getName(),
            'description' => $intent->getDescription(),
            'interpreter' => $intent->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $intent->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $intent->getUpdatedAt()->format(\DateTime::ISO8601),
            'speaker' => $intent->getSpeaker(),
            'confidence' => $intent->getConfidence(),
            'sample_utterance' => $intent->getSampleUtterance(),
            'transition' => ['conversation' => $intent->getTransition()->getConversation(),'scene' => $intent->getTransition()
            ->getScene(),'turn' => $intent->getTransition()->getTurn()],
            'expected_attributes' => $intent->getExpectedAttributes(),
            'actions' => [],
            'listens_for' => $intent->getListensFor(),
            'virtual_intents' => $intent->getVirtualIntents()->map(fn($i) => ['speaker' => $i->getSpeaker(), 'intentId' =>
            $i->getIntentId()])->toArray(),
            'turn' => null
        ];
        $this->assertEquals($expected, $data);
    }

    public function testDenormalizeStandaloneIntent() {
        $normalizers = [new IntentNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
        BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new TransitionNormalizer(), new VirtualIntentNormalizer(),
            new VirtualIntentCollectionNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $intent = $this->getStandaloneIntent();
        $data = [
            'type' => Intent::TYPE,
            'uid' => $intent->getUid(),
            'od_id' => $intent->getOdId(),
            'name' => $intent->getName(),
            'description' => $intent->getDescription(),
            'interpreter' => $intent->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $intent->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $intent->getUpdatedAt()->format(\DateTime::ISO8601),
            'speaker' => $intent->getSpeaker(),
            'confidence' => $intent->getConfidence(),
            'sample_utterance' => $intent->getSampleUtterance(),
            'transition' => ['conversation' => $intent->getTransition()->getConversation(),'scene' => $intent->getTransition()
                ->getScene(),'turn' => $intent->getTransition()->getTurn()],
            'expected_attributes' => $intent->getExpectedAttributes(),
            'actions' => [],
            'listens_for' => $intent->getListensFor(),
            'virtual_intents' => $intent->getVirtualIntents()->map(fn($i) => ['speaker' => $i->getSpeaker(), 'intentId' =>
                $i->getIntentId()])->toArray(),
            'turn' => null
        ];

        $denormalized = $serializer->denormalize($data, Turn::class);
        $this->assertEquals($denormalized, $intent);
    }

    public function testDenormalizeMissingType() {
        $normalizers = [new IntentNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
        BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new TransitionNormalizer(), new VirtualIntentNormalizer(),
            new VirtualIntentCollectionNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $intent = $this->getStandaloneIntent();
        $data = [
            'uid' => $intent->getUid(),
            'od_id' => $intent->getOdId(),
            'name' => $intent->getName(),
            'description' => $intent->getDescription(),
            'interpreter' => $intent->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $intent->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $intent->getUpdatedAt()->format(\DateTime::ISO8601),
            'speaker' => $intent->getSpeaker(),
            'confidence' => $intent->getConfidence(),
            'sample_utterance' => $intent->getSampleUtterance(),
            'transition' => ['conversation' => $intent->getTransition()->getConversation(),'scene' => $intent->getTransition()
                ->getScene(),'turn' => $intent->getTransition()->getTurn()],
            'expected_attributes' => $intent->getExpectedAttributes(),
            'actions' => [],
            'listens_for' => $intent->getListensFor(),
            'virtual_intents' => $intent->getVirtualIntents()->map(fn($i) => ['speaker' => $i->getSpeaker(), 'intentId' =>
                $i->getIntentId()])->toArray(),
            'turn' => null
        ];

        $this->expectException(NotNormalizableValueException::class);
        $denormalized = $serializer->denormalize($data, Turn::class);
    }

}
