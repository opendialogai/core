<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneCollectionNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

class ConversationSerializationTest extends SerializationTestCase
{
    public function testNormalizeStandaloneConversation()
    {
        $conversation = $this->getStandaloneConversation();
        $normalizers = [new ConversationNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($conversation, 'json', [AbstractNormalizer::ATTRIBUTES => array_merge
    (Conversation::localFields(),
            [Conversation::CONDITIONS => Condition::FIELDS, Conversation::BEHAVIORS => Behavior::FIELDS])]);
        $expected = [
            'id' => $conversation->getUid(),
            'od_id' => $conversation->getOdId(),
            'name' => $conversation->getName(),
            'description' => $conversation->getDescription(),
            'interpreter' => $conversation->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $conversation->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $conversation->getUpdatedAt()->format(\DateTime::ISO8601),
        ];
        $this->assertEquals($expected, $data);
    }

    public function testDenormalizeStandaloneConversation()
    {
        $normalizers = [
            new ConversationNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
            BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new SceneCollectionNormalizer()
        ];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $conversation = $this->getStandaloneConversation();
        $data = [
            'id' => $conversation->getUid(),
            'od_id' => $conversation->getOdId(),
            'name' => $conversation->getName(),
            'description' => $conversation->getDescription(),
            'interpreter' => $conversation->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $conversation->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $conversation->getUpdatedAt()->format(\DateTime::ISO8601),
        ];

        $denormalized = $serializer->denormalize($data, Conversation::class);
        $this->assertEquals($conversation, $denormalized);
    }

}
