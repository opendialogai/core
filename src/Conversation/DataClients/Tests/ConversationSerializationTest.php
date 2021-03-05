<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class ConversationSerializationTest extends SerializationTestCase
{
    public function testNormalizeStandaloneConversation()
    {
        $conversation = $this->getStandaloneConversation();
        $normalizers = [new ConversationNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($conversation, 'json', []);
        $expected = [
            'uid' => $conversation->getUid(),
            'od_id' => $conversation->getOdId(),
            'name' => $conversation->getName(),
            'description' => $conversation->getDescription(),
            'interpreter' => $conversation->getInterpreter(),
            'conditions' => [],
            'behaviors' => [],
            'created_at' => $conversation->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $conversation->getUpdatedAt()->format(\DateTime::ISO8601),
            'scenes' => [],
            'scenario' => null
        ];
        $this->assertEquals($expected, $data);
    }

}
