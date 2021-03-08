<?php

namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationObject;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\BehaviorsCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConditionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ConversationNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\IntentNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\ScenarioNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\SceneNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TransitionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\TurnNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentCollectionNormalizer;
use OpenDialogAi\Core\Conversation\DataClients\Serializers\VirtualIntentNormalizer;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

class ScenarioSerializationTest extends SerializationTestCase
{

    public function testNormalizeStandaloneScenario()
    {
        $scenario = $this->getStandaloneScenario();
        $normalizers = [new ScenarioNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($scenario, 'json', []);
        $expected = [
            'type' => Scenario::TYPE,
            'uid' => $scenario->getUid(),
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => ["STARTING"],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
            'conversations' => []
        ];
        $this->assertEquals($expected, $data);
    }


    public function testNormalizeFullScenarioGraph() {
        $scenario = $this->getStandaloneScenario();
        $conversation = $this->getStandaloneConversation();
        $scene = $this->getStandaloneScene();
        $turn = $this->getStandaloneTurn();
        $intent = $this->getStandaloneIntent();

        $scenario->addConversation($conversation);
        $conversation->setScenario($scenario);

        $conversation->addScene($scene);
        $scene->setConversation($conversation);

        $scene->addTurn($turn);
        $turn->setScene($scene);

        $turn->addRequestIntent($intent);
        $intent->setTurn($turn);

        $normalizers = [new ScenarioNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new
        ConversationNormalizer(),
        new
        SceneNormalizer(), new
        TurnNormalizer(),
            new IntentNormalizer(), new TransitionNormalizer(), new VirtualIntentNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($scenario, 'json', []);
        $expected = [
            'type' => Scenario::TYPE,
            'uid' => $scenario->getUid(),
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => ["STARTING"],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
            'conversations' => [[
                'type' => Conversation::TYPE,
                'scenario' => $scenario->getUid(),
                'uid' => $conversation->getUid(),
                'od_id' => $conversation->getOdId(),
                'name' => $conversation->getName(),
                'description' => $conversation->getDescription(),
                'interpreter' => $conversation->getInterpreter(),
                'conditions' => [],
                'behaviors' => [],
                'created_at' => $conversation->getCreatedAt()->format(\DateTime::ISO8601),
                'updated_at' => $conversation->getUpdatedAt()->format(\DateTime::ISO8601),
                'scenes' => [[
                    'type' => Scene::TYPE,
                    'conversation' => $conversation->getUid(),
                    'uid' => $scene->getUid(),
                    'od_id' => $scene->getOdId(),
                    'name' => $scene->getName(),
                    'description' => $scene->getDescription(),
                    'interpreter' => $scene->getInterpreter(),
                    'conditions' => [],
                    'behaviors' => [],
                    'created_at' => $scene->getCreatedAt()->format(\DateTime::ISO8601),
                    'updated_at' => $scene->getUpdatedAt()->format(\DateTime::ISO8601),
                    'turns' => [[
                        'type' => Turn::TYPE,
                        'scene' => $scene->getUid(),
                        'uid' => $turn->getUid(),
                        'od_id' => $turn->getOdId(),
                        'name' => $turn->getName(),
                        'description' => $turn->getDescription(),
                        'interpreter' => $turn->getInterpreter(),
                        'conditions' => [],
                        'behaviors' => [],
                        'created_at' => $turn->getCreatedAt()->format(\DateTime::ISO8601),
                        'updated_at' => $turn->getUpdatedAt()->format(\DateTime::ISO8601),
                        'valid_origins' => $turn->getValidOrigins(),
                        'request_intents' => [[
                            'type' => Intent::TYPE,
                            'turn' => $turn->getUid(),
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
                            'transition' => ['conversation' => $intent->getTransition()->getConversation(),'scene' => $intent->getTransition()
                                ->getScene(),'turn' => $intent->getTransition()->getTurn()],
                            'listens_for' => $intent->getListensFor(),
                            'virtual_intents' => $intent->getVirtualIntents()->map(fn($i) => ['speaker' => $i->getSpeaker(), 'intentId' =>
                                $i->getIntentId()])->toArray(),
                            'expected_attributes' => $intent->getExpectedAttributes(),
                            'actions' => []
                        ]],
                        'response_intents' => [],
                    ]]
                ]]
            ]]
        ];
        $this->assertEquals($expected, $data);
    }

    public function testDenormalizeStandaloneScenario() {
        $normalizers = [new ScenarioNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
        BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new ConversationCollectionNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $scenario = $this->getStandaloneScenario();
        $data = [
            'type' => Scenario::TYPE,
            'uid' => $scenario->getUid(),
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => [Behavior::STARTING],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
            'conversations' => []
        ];

        $denormalized = $serializer->denormalize($data, Scenario::class);
        $this->assertEquals($denormalized, $scenario);
    }

    public function testDenormalizeMissingType() {
        $normalizers = [new ScenarioNormalizer(), new ConditionCollectionNormalizer(), new ConditionNormalizer(), new
        BehaviorsCollectionNormalizer(), new BehaviorNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $scenario = $this->getStandaloneScenario();
        $data = [
            'uid' => $scenario->getUid(),
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => [Behavior::STARTING],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
            'conversations' => []
        ];

        $this->expectException(NotNormalizableValueException::class);
        $denormalized = $serializer->denormalize($data, Scenario::class);
    }

    public function testDenormalizeFullScenarioGraph() {
        $scenario = $this->getStandaloneScenario();
        $conversation = $this->getStandaloneConversation();
        $scene = $this->getStandaloneScene();
        $turn = $this->getStandaloneTurn();
        $intent = $this->getStandaloneIntent();

        $scenario->addConversation($conversation);
        $conversation->setScenario($scenario);

        $conversation->addScene($scene);
        $scene->setConversation($conversation);

        $scene->addTurn($turn);
        $turn->setScene($scene);

        $turn->addRequestIntent($intent);
        $intent->setTurn($turn);

        $normalizers = [new ScenarioNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer(), new
        ConversationNormalizer(),
            new ConversationCollectionNormalizer(),
            new SceneCollectionNormalizer(),
            new TurnCollectionNormalizer(),
            new IntentCollectionNormalizer(),
            new
            SceneNormalizer(), new
            TurnNormalizer(),
            new IntentNormalizer(), new TransitionNormalizer(), new VirtualIntentNormalizer(), new
            VirtualIntentCollectionNormalizer(), new
            ConditionCollectionNormalizer(), new ConditionNormalizer(), new BehaviorsCollectionNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = [
            'type' => Scenario::TYPE,
            'uid' => $scenario->getUid(),
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => ["STARTING"],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
            'conversations' => [[
                'type' => Conversation::TYPE,
                'scenario' => $scenario->getUid(),
                'uid' => $conversation->getUid(),
                'od_id' => $conversation->getOdId(),
                'name' => $conversation->getName(),
                'description' => $conversation->getDescription(),
                'interpreter' => $conversation->getInterpreter(),
                'conditions' => [],
                'behaviors' => [],
                'created_at' => $conversation->getCreatedAt()->format(\DateTime::ISO8601),
                'updated_at' => $conversation->getUpdatedAt()->format(\DateTime::ISO8601),
                'scenes' => [[
                    'type' => Scene::TYPE,
                    'conversation' => $conversation->getUid(),
                    'uid' => $scene->getUid(),
                    'od_id' => $scene->getOdId(),
                    'name' => $scene->getName(),
                    'description' => $scene->getDescription(),
                    'interpreter' => $scene->getInterpreter(),
                    'conditions' => [],
                    'behaviors' => [],
                    'created_at' => $scene->getCreatedAt()->format(\DateTime::ISO8601),
                    'updated_at' => $scene->getUpdatedAt()->format(\DateTime::ISO8601),
                    'turns' => [[
                        'type' => Turn::TYPE,
                        'scene' => $scene->getUid(),
                        'uid' => $turn->getUid(),
                        'od_id' => $turn->getOdId(),
                        'name' => $turn->getName(),
                        'description' => $turn->getDescription(),
                        'interpreter' => $turn->getInterpreter(),
                        'conditions' => [],
                        'behaviors' => [],
                        'created_at' => $turn->getCreatedAt()->format(\DateTime::ISO8601),
                        'updated_at' => $turn->getUpdatedAt()->format(\DateTime::ISO8601),
                        'valid_origins' => $turn->getValidOrigins(),
                        'request_intents' => [[
                            'type' => Intent::TYPE,
                            'turn' => $turn->getUid(),
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
                            'transition' => ['conversation' => $intent->getTransition()->getConversation(),'scene' => $intent->getTransition()
                                ->getScene(),'turn' => $intent->getTransition()->getTurn()],
                            'listens_for' => $intent->getListensFor(),
                            'virtual_intents' => $intent->getVirtualIntents()->map(fn($i) => ['speaker' => $i->getSpeaker(), 'intentId' =>
                                $i->getIntentId()])->toArray(),
                            'expected_attributes' => $intent->getExpectedAttributes(),
                            'actions' => []
                        ]],
                        'response_intents' => [],
                    ]]
                ]]
            ]]
        ];
        $denormalized = $serializer->denormalize($data, Scenario::class);
        $this->assertEquals($scenario, $denormalized);
    }


    public function testSerializeScenarioLocalFields() {
        $scenario = $this->getStandaloneScenario();
        $normalizers = [new ScenarioNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($scenario, 'json', [AbstractNormalizer::ATTRIBUTES => Scenario::localFields()]);
        $expected = [
            'type' => Scenario::TYPE,
            'uid' => $scenario->getUid(),
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => ["STARTING"],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
        ];
        $this->assertEquals($expected, $data);
    }

    public function testSerializeScenarioLocalFieldsNoUID() {
        $scenario = $this->getStandaloneScenario();
        $normalizers = [new ScenarioNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($scenario, 'json', [AbstractNormalizer::ATTRIBUTES => Scenario::localFields()]);
        $expected = [
            'type' => Scenario::TYPE,
            'od_id' => $scenario->getOdId(),
            'name' => $scenario->getName(),
            'description' => $scenario->getDescription(),
            'interpreter' => $scenario->getInterpreter(),
            'conditions' => [],
            'behaviors' => ["STARTING"],
            'active' => false,
            'status' => Scenario::DRAFT_STATUS,
            'created_at' => $scenario->getCreatedAt()->format(\DateTime::ISO8601),
            'updated_at' => $scenario->getUpdatedAt()->format(\DateTime::ISO8601),
        ];
        $this->assertEquals($expected, $data);
    }

    public function testSerializeScenarioNameOnly() {
        $scenario = $this->getStandaloneScenario();
        $normalizers = [new ScenarioNormalizer(), new BehaviorsCollectionNormalizer(), new BehaviorNormalizer()];
        $encoders = [new JsonEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->normalize($scenario, 'json', [AbstractNormalizer::ATTRIBUTES => [ConversationObject::NAME]]);
        $expected = [
            'name' => $scenario->getName(),
        ];
        $this->assertEquals($expected, $data);
    }

}
