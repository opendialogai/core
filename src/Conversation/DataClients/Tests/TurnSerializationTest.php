<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnNormalizer;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;

class TurnSerializationTest extends SerializationTestCase
{

    public function testNormalizeStandaloneTurn()
    {
        $turn = $this->getStandaloneTurn();
        $normalizers = [new TurnNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($turn, 'json', []);
        $expected = [
            'type' => Turn::TYPE, 'uid' => $turn->getUid(), 'od_id' => $turn->getOdId(), 'name' => $turn->getName(),
            'description' => $turn->getDescription(), 'interpreter' => $turn->getInterpreter(), 'conditions' => [],
            'behaviors' => [], 'created_at' => $turn->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $turn->getUpdatedAt()->format(\DateTime::ISO8601),
            'valid_origins' => $turn->getValidOrigins(), 'scene' => null
        ];
        $this->assertEquals($expected, $data);
    }

    public function testDenormalizeStandaloneTurn()
    {
        $normalizers = [
            new TurnNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new IntentCollectionNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $turn = $this->getStandaloneTurn();
        $data = [
            'type' => Turn::TYPE, 'uid' => $turn->getUid(), 'od_id' => $turn->getOdId(), 'name' => $turn->getName(),
            'description' => $turn->getDescription(), 'interpreter' => $turn->getInterpreter(), 'conditions' => [],
            'behaviors' => [], 'created_at' => $turn->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $turn->getUpdatedAt()->format(\DateTime::ISO8601), 'valid_origins' => $turn->getValidOrigins(),
            'request_intents' => [], 'response_intents' => [], 'scene' => null
        ];

        $denormalized = $serializer->denormalize($data, Turn::class);
        $this->assertEquals($denormalized, $turn);
    }

    public function testDenormalizeMissingType()
    {
        $normalizers = [
            new TurnNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $turn = $this->getStandaloneTurn();
        $data = [
            'uid' => $turn->getUid(), 'od_id' => $turn->getOdId(), 'name' => $turn->getName(),
            'description' => $turn->getDescription(), 'interpreter' => $turn->getInterpreter(), 'conditions' => [],
            'behaviors' => [], 'created_at' => $turn->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $turn->getUpdatedAt()->format(\DateTime::ISO8601), 'valid_origins' => $turn->getValidOrigins(),
            'request_intents' => [], 'response_intents' => [], 'scene' => null
        ];

        $this->expectException(NotNormalizableValueException::class);
        $denormalized = $serializer->denormalize($data, Turn::class);
    }

}
