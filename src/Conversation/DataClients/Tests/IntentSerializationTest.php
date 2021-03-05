<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class IntentSerializationTest extends SerializationTestCase
{

    public function testNormalizeStandaloneIntent()
    {
        $intent = $this->getStandaloneIntent();
        $normalizers = [new IntentNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($intent, 'json', []);
        $expected = [
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
            'transition' => $intent->getTransition()->toArray(),
            'expected_attributes' => $intent->getExpectedAttributes(),
            'actions' => [],
            'listens_for' => $intent->getListensFor(),
            'virtual_intents' => array_map(fn($vi) => $vi->toArray(), $intent->getVirtualIntents()),
            'turn' => null
        ];
        $this->assertEquals($expected, $data);
    }

}
