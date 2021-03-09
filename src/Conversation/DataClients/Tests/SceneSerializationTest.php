<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnCollectionNormalizer;
use OpenDialogAi\Core\Conversation\Scene;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
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
            'type' => Scene::TYPE, 'uid' => $scene->getUid(), 'od_id' => $scene->getOdId(), 'name' => $scene->getName(),
            'description' => $scene->getDescription(), 'interpreter' => $scene->getInterpreter(), 'conditions' => [],
            'behaviors' => [], 'created_at' => $scene->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scene->getUpdatedAt()->format(\DateTime::ISO8601), 'conversation' => null
        ];
        $this->assertEquals($expected, $data);
    }

    public function testDenormalizeStandaloneScene()
    {
        $normalizers = [
            new SceneNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new TurnCollectionNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $scene = $this->getStandaloneScene();
        $data = [
            'type' => Scene::TYPE, 'uid' => $scene->getUid(), 'od_id' => $scene->getOdId(), 'name' => $scene->getName(),
            'description' => $scene->getDescription(), 'interpreter' => $scene->getInterpreter(), 'conditions' => [],
            'behaviors' => [], 'created_at' => $scene->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scene->getUpdatedAt()->format(\DateTime::ISO8601), 'turns' => [], 'conversation' => null
        ];

        $denormalized = $serializer->denormalize($data, Scene::class);
        $this->assertEquals($denormalized, $scene);
    }

    public function testDenormalizeNoType()
    {
        $normalizers = [
            new SceneNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $scene = $this->getStandaloneScene();
        $data = [
            'uid' => $scene->getUid(), 'od_id' => $scene->getOdId(), 'name' => $scene->getName(),
            'description' => $scene->getDescription(), 'interpreter' => $scene->getInterpreter(), 'conditions' => [],
            'behaviors' => [], 'created_at' => $scene->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scene->getUpdatedAt()->format(\DateTime::ISO8601), 'turns' => [], 'conversation' => null
        ];

        $this->expectException(NotNormalizableValueException::class);
        $denormalized = $serializer->denormalize($data, Scene::class);
    }

}
