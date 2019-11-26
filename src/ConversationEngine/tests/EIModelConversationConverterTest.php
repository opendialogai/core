<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationQueryFactoryInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelToGraphConverter;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Tests\TestCase;

class EIModelConversationConverterTest extends TestCase
{
    /**
     * @var EIModelCreator
     */
    private $eiModelCreator;

    /**
     * @var DGraphClient
     */
    private $dGraph;

    /**
     * @var ConversationQueryFactoryInterface
     */
    private $queryFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->eiModelCreator = app()->make(EIModelCreator::class);
        $this->dGraph = app()->make(DGraphClient::class);
        $this->queryFactory = app()->make(ConversationQueryFactoryInterface::class);

        $attributes = ['test' => IntAttribute::class];
        AttributeResolver::registerAttributes($attributes);

        $this->activateConversation($this->conversation1());
        $this->activateConversation($this->conversation2());
    }

    public function testBuildConversation()
    {
        /* @var ConversationStoreInterface $conversationStore */
        $conversationStore = app()->make(ConversationStoreInterface::class);

        /* @var \OpenDialogAi\ConversationBuilder\Conversation $conversationModel */
        $conversationModel = \OpenDialogAi\ConversationBuilder\Conversation::where('name', 'hello_bot_world')->first();

        /* @var EIModelConversation $conversationModel */
        $conversationEIModel = $conversationStore->getEIModelConversation($conversationModel->graph_uid);

        /* @var EIModelToGraphConverter $converter */
        $converter = app()->make(EIModelToGraphConverter::class);

        /* @var Conversation $conversation */
        $conversation = $converter->convertConversation($conversationEIModel);

        // General
        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('hello_bot_world', $conversation->getId());

        // Conditions
        $conditions = $conversation->getConditions();
        $this->assertCount(2, $conditions);

        /* @var Condition $firstCondition */
        $firstCondition = $conditions->get('user.name-is_not_set-');
        $this->assertEquals('user.name-is_not_set-', $firstCondition->getId());
        $this->assertEquals('is_not_set', $firstCondition->getAttribute(Model::OPERATION)->getValue());
        $this->assertEmpty($firstCondition->getAttribute(Model::PARAMETERS)->getValue());

        /* @var Condition $secondCondition */
        $secondCondition = $conditions->get('user.test-gt-10');
        $this->assertEquals('user.test-gt-10', $secondCondition->getId());
        $this->assertEquals('gt', $secondCondition->getAttribute(Model::OPERATION)->getValue());
        $this->assertNotNull($secondCondition->getAttribute(Model::PARAMETERS)->getValue());
        $this->assertEquals(10, $secondCondition->getAttribute(Model::PARAMETERS)->getValue()['value']);

        // Opening scene
        $openingScenes = $conversation->getOpeningScenes();
        $this->assertCount(1, $openingScenes);

        /* @var Scene $openingScene */
        $openingScene = $openingScenes->first()->value;

        $this->assertEquals('opening_scene', $openingScene->getId());
        $this->assertCount(1, $openingScene->getIntentsSaidByUser());
        $this->assertCount(2, $openingScene->getIntentsSaidByBot());

        /* @var Intent $firstIntent */
        $firstIntent = $openingScene->getIntentsSaidByUserInOrder()->first()->value;
        /* @var Intent $secondIntent */
        $secondIntent = $openingScene->getIntentsSaidByBotInOrder()->first()->value;
        /* @var Intent $thirdIntent */
        $thirdIntent = $openingScene->getIntentsSaidByBotInOrder()->skip(1)->value;

        $this->assertEquals('hello_bot', $firstIntent->getId());
        $this->assertEquals('action.core.example', $firstIntent->getAction()->getId());
        $this->assertTrue($firstIntent->hasInterpreter());
        $this->assertEquals('interpreter.core.callbackInterpreter', $firstIntent->getInterpreter()->getId());
        $this->assertCount(0, $firstIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES));
        $this->assertEquals(1, $firstIntent->getConfidence());
        $this->assertFalse($firstIntent->completes());
        $this->assertFalse($firstIntent->hasExpectedAttributes());

        $this->assertEquals('hello_user', $secondIntent->getId());
        $this->assertEquals('action.core.example', $secondIntent->getAction()->getId());
        $this->assertFalse($secondIntent->hasInterpreter());
        $this->assertCount(1, $secondIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES));
        $secondTransition = $secondIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES)->first();
        $this->assertEquals('user_participant_in_scene2', $secondTransition->key);
        $this->assertEquals(1, $secondIntent->getConfidence());
        $this->assertFalse($secondIntent->completes());
        $this->assertFalse($secondIntent->hasExpectedAttributes());

        $this->assertEquals('hello_registered_user', $thirdIntent->getId());
        $this->assertEquals('action.core.example', $thirdIntent->getAction()->getId());
        $this->assertFalse($thirdIntent->hasInterpreter());
        $this->assertCount(1, $thirdIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES));
        $thirdTransition = $thirdIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES)->first();
        $this->assertEquals('user_participant_in_scene3', $thirdTransition->key);
        $this->assertEquals(1, $thirdIntent->getConfidence());
        $this->assertFalse($thirdIntent->completes());
        $this->assertFalse($thirdIntent->hasExpectedAttributes());

        // Scene 4
        $otherScenes = $conversation->getNonOpeningScenes();
        $this->assertCount(3, $otherScenes);

        /* @var Scene $scene4 */
        $scene4 = $otherScenes->get('scene4');

        $this->assertEquals('scene4', $scene4->getId());
        $this->assertCount(1, $scene4->getIntentsSaidByUser());
        $this->assertCount(1, $scene4->getIntentsSaidByBot());

        /* @var Intent $botIntent */
        $botIntent = $scene4->getIntentsSaidByBotInOrder()->first()->value;
        /* @var Intent $userIntent */
        $userIntent = $scene4->getIntentsSaidByUserInOrder()->first()->value;

        $this->assertEquals('intent.core.example', $botIntent->getId());
        $this->assertFalse($botIntent->causesAction());
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertCount(0, $botIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES));
        $this->assertEquals(1, $botIntent->getConfidence());
        $this->assertFalse($botIntent->completes());
        $this->assertFalse($botIntent->hasExpectedAttributes());

        $this->assertEquals('intent.core.example2', $userIntent->getId());
        $this->assertFalse($userIntent->causesAction());
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertEquals('interpreter.core.callbackInterpreter', $userIntent->getInterpreter()->getId());
        $this->assertCount(1, $userIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES));
        $secondTransition = $userIntent->getNodesConnectedByIncomingRelationship(Model::LISTENS_FOR_ACROSS_SCENES)->first();
        $this->assertEquals('bot_participant_in_scene3', $secondTransition->key);
        $this->assertEquals(1, $userIntent->getConfidence());
        $this->assertFalse($userIntent->completes());
        $this->assertTrue($userIntent->hasExpectedAttributes());
        $this->assertCount(1, $userIntent->getExpectedAttributes());
        $this->assertEquals('user.name', $userIntent->getExpectedAttributes()[0]->getId());
    }
}
