<?php


namespace OpenDialogAi\ConversationBuilder\tests;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Attribute\Operation\GreaterThanOperation;
use OpenDialogAi\Core\Attribute\Operation\IsSetOperation;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationConditionTest extends TestCase
{
    private $goodConditions;

    private $userNameCondition;

    private $userTestCondition;

    /* @var ConversationManager */
    private $cm;

    /* @var Conversation $conversationModel */
    private $conversationModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->userNameCondition = [
            'condition' => [
                'attribute' => 'user.name',
                'operation' => IsSetOperation::NAME
            ]
        ];

        $this->userTestCondition = [
            'condition' => [
                'attribute' => 'user.test',
                'operation' => GreaterThanOperation::NAME,
                'parameters' => [
                    'value' => 10
                ]
            ]
        ];


        $this->goodConditions = [
          $this->userNameCondition,
          $this->userTestCondition
        ];

        Conversation::create(['name' => 'Test Conversation', 'model' => 'conversation:']);
        /* @var \OpenDialogAi\ConversationBuilder\Conversation $conversation */
        $this->conversationModel = Conversation::where('name', 'Test Conversation')->first();

        $this->cm = new ConversationManager('TestConversation');

        /* @var AttributeResolver $attributeResolver */
        $attributeResolver = $this->app->make(AttributeResolver::class);
        $attributes = ['test' => IntAttribute::class];
        $attributeResolver->registerAttributes($attributes);
    }

    public function testConditionsAreCreatedCorrectly()
    {
        Conversation::create(['name' => 'Test Conversation 2', 'model' => 'conversation:']);
        /* @var \OpenDialogAi\ConversationBuilder\Conversation $conversation */
        $conversationModel = Conversation::where('name', 'Test Conversation')->first();

        $conversationModel->addConversationConditions($this->goodConditions, $this->cm);

        $conversation = $this->cm->getConversation();

        $this->assertCount(2, $conversation->getConditions());

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

    public function testConditionAttributeNotSupported()
    {
        $unSupportedCondition = [
            'condition' => [
                'attribute' => 'user.notdefined',
                'operation' => GreaterThanOperation::NAME,
                'parameters' => [
                    'value' => 10
                ]
            ]
        ];

        $conditionsToAdd = [
            $unSupportedCondition,
        ];

        Log::shouldReceive('debug')
            ->with('Could not create condition because: Attribute user.notdefined could not be resolved');

        $this->conversationModel->addConversationConditions($conditionsToAdd, $this->cm);
    }

    public function testConditionOperationNotSupported()
    {
        $unSupportedCondition = [
            'condition' => [
                'attribute' => 'user.name',
                'operation' => 'crazy_op',
                'parameters' => [
                    'value' => 10
                ]
            ]
        ];

        $conditionsToAdd = [
            $unSupportedCondition,
        ];

        /*Log::shouldReceive('debug')
            ->with('Could not create condition because: Condition operation crazy_op is not a valid operation');*/

        $this->conversationModel->addConversationConditions($conditionsToAdd, $this->cm);
    }

    public function testConditionRequiresValue()
    {
        $unSupportedCondition = [
            'condition' => [
                'attribute' => 'user.name',
                'operation' => GreaterThanOperation::NAME,
            ]
        ];

        $conditionsToAdd = [
            $unSupportedCondition,
        ];

        /*Log::shouldReceive('debug')
            ->with('Created condition from Yaml.');

        Log::shouldReceive('debug')
            ->with('Could not create condition because: Condition user.name required a value but has not defined it');*/

        $this->conversationModel->addConversationConditions($conditionsToAdd, $this->cm);
    }

    public function testConditionRequiresOperation()
    {
        $unSupportedCondition = [
            'condition' => [
                'attribute' => 'user.name',
                'value' => 'john'
            ]
        ];

        $conditionsToAdd = [
            $unSupportedCondition,
        ];

        Log::shouldReceive('debug')
            ->with('Could not create condition because: Condition user.name does not define an operation');

        $this->conversationModel->addConversationConditions($conditionsToAdd, $this->cm);
    }
}
