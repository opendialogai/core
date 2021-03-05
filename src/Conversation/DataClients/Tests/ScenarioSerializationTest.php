<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\Scenario;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class ScenarioSerializationTest extends SerializationTestCase
{

    public function testNormalizeStandaloneScenario()
    {
        $scenario = $this->getStandaloneScenario();
        $normalizers = [new ScenarioNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($scenario, 'json', []);
        $expected = [
            'uid' => $scenario->getUid(),
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
            'conversations' => []
        ];
        $this->assertEquals($expected, $data);
    }

}
