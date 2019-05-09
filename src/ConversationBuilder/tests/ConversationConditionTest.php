<?php


namespace OpenDialogAi\ConversationBuilder\tests;

use Illuminate\Support\Facades\Log;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
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
                'operation' => AbstractAttribute::IS_SET
            ]
        ];

        $this->userTestCondition = [
            'condition' => [
                'attribute' => 'user.test',
                'operation' => AbstractAttribute::GREATER_THAN,
                'value' => 10
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
            $attribute = $condition->getAttributeToCompareAgainst();
            $this->assertTrue(in_array($attribute->getId(), ['name', 'test']));

            if ($condition->getId() == 'user.name-is_set-') {
                $this->assertInstanceOf(StringAttribute::class, $condition->getAttributeToCompareAgainst());
                $this->assertTrue($condition->getAttributeToCompareAgainst()->getValue() === null);
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_NAME)->getValue() === 'name');
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_VALUE)->getValue() === null);
                $this->assertTrue($condition->getEvaluationOperation() == AbstractAttribute::IS_SET);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == AbstractAttribute::IS_SET);
            }

            if ($condition->getId() == 'user.test-gt-10') {
                $this->assertInstanceOf(IntAttribute::class, $condition->getAttributeToCompareAgainst());
                $this->assertTrue($condition->getAttributeToCompareAgainst()->getValue() === 10);
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_VALUE)->getValue() === 10);
                $this->assertTrue($condition->getAttribute(Model::ATTRIBUTE_NAME)->getValue() === 'test');
                $this->assertTrue($condition->getEvaluationOperation() == AbstractAttribute::GREATER_THAN);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == AbstractAttribute::GREATER_THAN);
            }
        }
    }

    public function testConditionAttributeNotSupported()
    {
        $unSupportedCondition = [
            'condition' => [
                'attribute' => 'user.notdefined',
                'operation' => AbstractAttribute::GREATER_THAN,
                'value' => 10
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
                'value' => 10
            ]
        ];

        $conditionsToAdd = [
            $unSupportedCondition,
        ];

        Log::shouldReceive('debug')
            ->with('Could not create condition because: Condition operation crazy_op is not a valid operation');

        $this->conversationModel->addConversationConditions($conditionsToAdd, $this->cm);

    }

    public function testConditionRequiresValue()
    {
        $unSupportedCondition = [
            'condition' => [
                'attribute' => 'user.name',
                'operation' => AbstractAttribute::GREATER_THAN,
            ]
        ];

        $conditionsToAdd = [
            $unSupportedCondition,
        ];

        Log::shouldReceive('debug')
            ->with('Created condition from Yaml.');

        Log::shouldReceive('debug')
            ->with('Could not create condition because: Condition user.name required a value but has not defined it');

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
