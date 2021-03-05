<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneNormalizer;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
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
            'uid' => $turn->getUid(),
            'od_id' => $turn->getOdId(),
            'name' => $turn->getName(),
            'description' => $turn->getDescription(),
            'interpreter' => $turn->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $turn->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $turn->getUpdatedAt()->format(\DateTime::ISO8601),
            'request_intents' => [],
            'response_intents' => [],
            'valid_origins' => $turn->getValidOrigins(),
            'scene' => null
        ];
        $this->assertEquals($expected, $data);
    }

}
