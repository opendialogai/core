<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneNormalizer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class SceneSerializationTest extends SerializationTestCase
{

    public function testNormalizeStandaloneScene()
    {
        $scene = $this->getStandaloneScene();
        $normalizers = [new SceneNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($scene, 'json', []);
        $expected = [
            'uid' => $scene->getUid(),
            'od_id' => $scene->getOdId(),
            'name' => $scene->getName(),
            'description' => $scene->getDescription(),
            'interpreter' => $scene->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $scene->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scene->getUpdatedAt()->format(\DateTime::ISO8601),
            'turns' => [],
            'conversation' => null
        ];
        $this->assertEquals($expected, $data);
    }

}
