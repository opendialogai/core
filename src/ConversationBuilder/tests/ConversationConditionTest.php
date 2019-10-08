<?php

namespace OpenDialogAi\ConversationBuilder\tests;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Attribute\IntAttribute;
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
                'operation' => IsSetOperation::NAME
            ]
        ];

        $this->userTestCondition = [
            'condition' => [
                'attributes' => [
                    'usertest' => 'user.test'
                ],
                'operation' => GreaterThanOperation::NAME,
                'parameters' => [
                    'value' => 10
                ]
            ]
        ];

        $this->userLastSeenCondition = [
            'condition' => [
                'attribute' => 'user.last_seen',
                'operation' => TimePassedGreaterThanOperation::NAME,
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

        $this->cm = new ConversationManager('TestConversation');

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
                $this->assertTrue($condition->getEvaluationOperation() == IsSetOperation::NAME);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == IsSetOperation::NAME);
            }

            if ($condition->getId() == 'user.test-gt-10') {
                $this->assertTrue($condition->getEvaluationOperation() == GreaterThanOperation::NAME);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == GreaterThanOperation::NAME);
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
