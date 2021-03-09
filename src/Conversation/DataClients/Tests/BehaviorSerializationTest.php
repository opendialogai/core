<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Tests\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class BehaviorSerializationTest extends TestCase
{
    public function testNormalizeBehavior()
    {

        $serializer = new Serializer([new BehaviorNormalizer()], [new JsonEncoder()]);

        $behavior = new Behavior(Behavior::STARTING);
        $data = $serializer->normalize($behavior, 'json', []);
        $this->assertEquals(Behavior::STARTING, $data);
    }

    public function testDenormalizeBehavior()
    {
        $serializer = new Serializer([new BehaviorNormalizer()], [new JsonEncoder()]);

        $behavior = new Behavior(Behavior::STARTING);

        $data = Behavior::STARTING;
        $denormalized = $serializer->denormalize($data, Behavior::class);
        $this->assertEquals($behavior, $denormalized);
    }
}
