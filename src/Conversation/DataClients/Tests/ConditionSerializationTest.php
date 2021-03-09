<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Tests\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class ConditionSerializationTest extends TestCase
{

    /**
     * @group skip
     */
    public function testNormalizeCondition()
    {

        /* TODO: Update when conditions can be serialized */
        $condition = new Condition('gte', ['user.age'], [25]);
        $serializer = new Serializer([new ConditionNormalizer()], [new JsonEncoder()]);
        $data = $serializer->normalize($condition, 'json', []);
        $this->assertEquals(null, $data);
    }

    /**
     * @group skip
     */
    public function testDenormalize()
    {
        /* TODO: Update when conditions can be serialized */
        $condition = new Condition('gte', ['user.age'], [25]);
        $serializer = new Serializer([new ConditionNormalizer()], [new JsonEncoder()]);

        $data = null;
        $denormalized = $serializer->denormalize($data, Condition::class);
        $this->assertEquals($condition, $denormalized);

    }
}
