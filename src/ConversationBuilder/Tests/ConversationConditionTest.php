<?php

namespace OpenDialogAi\ConversationBuilder\Tests;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\OperationEngine\Operations\GreaterThanOperation;
use OpenDialogAi\OperationEngine\Operations\IsSetOperation;
use OpenDialogAi\OperationEngine\Operations\TimePassedGreaterThanOperation;

class ConversationConditionTest extends TestCase
{
    private $goodConditions;

    private $userNameCondition;

    private $userTestCondition;

    private $userLastSeenCondition;

    /* @var ConversationManager */
    private $cm;

    /* @var Conversation $conversationModel */
    private $conversationModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->userNameCondition = [
            'condition' => [
                'attributes' => [
                    'username' => 'user.name'
                ],
                'operation' => IsSetOperation::$name
            ]
        ];

        $this->userTestCondition = [
            'condition' => [
                'attributes' => [
                    'usertest' => 'user.test'
                ],
                'operation' => GreaterThanOperation::$name,
                'parameters' => [
                    'value' => 10
                ]
            ]
        ];

        $this->userLastSeenCondition = [
            'condition' => [
                'attribute' => 'user.last_seen',
                'operation' => TimePassedGreaterThanOperation::$name,
                'value' => 600
            ]
        ];

        $this->goodConditions = [
          $this->userNameCondition,
          $this->userTestCondition,
          $this->userLastSeenCondition
        ];

        Conversation::create(['name' => 'Test Conversation', 'model' => 'conversation:']);
        /* @var \OpenDialogAi\ConversationBuilder\Conversation $conversation */
        $this->conversationModel = Conversation::where('name', 'Test Conversation')->first();

        $this->cm = new ConversationManager('TestConversation', ConversationNode::ACTIVATED, 0);

        $attributes = ['test' => IntAttribute::class];
        AttributeResolver::registerAttributes($attributes);
    }

    public function testConditionsAreCreatedCorrectly()
    {
        Conversation::create(['name' => 'Test Conversation 2', 'model' => 'conversation:']);
        /* @var \OpenDialogAi\ConversationBuilder\Conversation $conversation */
        $conversationModel = Conversation::where('name', 'Test Conversation')->first();

        $conversationModel->addConversationConditions($this->goodConditions, $this->cm);

        $conversation = $this->cm->getConversation();

        $this->assertCount(3, $conversation->getConditions());

        $conditions = $conversation->getConditions();

        /* @var \OpenDialogAi\Core\Conversation\Condition $condition */
        foreach ($conditions as $condition) {
            if ($condition->getId() == 'user.name-is_set-') {
                $this->assertTrue($condition->getEvaluationOperation() == IsSetOperation::$name);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == IsSetOperation::$name);
            }

            if ($condition->getId() == 'user.test-gt-10') {
                $this->assertTrue($condition->getEvaluationOperation() == GreaterThanOperation::$name);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == GreaterThanOperation::$name);
            }
        }
    }

    public function testConditionRequiresOperation()
    {
        $unSupportedCondition = [
            'condition' => [
                'attributes' => [
                    'username' => 'user.name'
                ],
                'value' => 'john'
            ]
        ];

        $conditionsToAdd = [
            $unSupportedCondition,
        ];

        Log::shouldReceive('debug')
            ->with('Could not create condition because: Condition does not define an operation');

        $this->conversationModel->addConversationConditions($conditionsToAdd, $this->cm);
    }
}
