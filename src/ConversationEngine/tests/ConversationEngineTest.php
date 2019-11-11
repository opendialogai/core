<?php

namespace OpenDialogAi\ConversationEngine\tests;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\AttributeResolver as AttributeResolverFacade;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelToGraphConverter;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;
use OpenDialogAi\Core\Utterances\Webchat\WebchatChatOpenUtterance;
use OpenDialogAi\InterpreterEngine\Interpreters\CallbackInterpreter;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\OperationEngine\Operations\GreaterThanOperation;
use OpenDialogAi\OperationEngine\Operations\IsSetOperation;

class ConversationEngineTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    /* @var WebchatChatOpenUtterance */
    private $utterance;

    public function setUp(): void
    {
        parent::setUp();
        /* @var AttributeResolver $attributeResolver */
        $attributeResolver = resolve(AttributeResolver::class);
        $attributes = [
            'test' => IntAttribute::class,
            'user_name' => StringAttribute::class,
            'user_email' => StringAttribute::class
        ];
        $attributeResolver->registerAttributes($attributes);

        $this->conversationEngine = resolve(ConversationEngineInterface::class);

        for ($i = 1; $i <= 4; $i++) {
            $conversationId = 'conversation' . $i;
            $this->activateConversation($this->$conversationId());
        }

        $this->utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
    }

    /**
     * @throws BindingResolutionException
     * @throws EIModelCreatorException
     */
    public function testConversationStoreIntents()
    {
        $conversationStore = $this->conversationEngine->getConversationStore();

        $openingIntents = $conversationStore->getAllEIModelOpeningIntents();
        $this->assertCount(4, $openingIntents);

        // Ensure deactivation is handled correctly
        /** @var Conversation $conversation */
        $conversation = Conversation::where('name', 'hello_bot_world')->first();

        $this->assertTrue($conversation->deactivateConversation());

        $openingIntents = $conversationStore->getAllEIModelOpeningIntents();
        $this->assertCount(3, $openingIntents);

        $this->assertTrue($conversation->activateConversation($conversation));

        $openingIntents = $conversationStore->getAllEIModelOpeningIntents();
        $this->assertCount(4, $openingIntents);

        $this->activateConversation($this->conversationWithManyOpeningIntents());

        $openingIntents = $conversationStore->getAllEIModelOpeningIntents();
        $this->assertCount(7, $openingIntents);
    }

    /**
     * @throws BindingResolutionException
     * @throws EIModelCreatorException
     */
    public function testConversationConditions()
    {
        /* @var ConversationStoreInterface $conversationStore */
        $conversationStore = $this->conversationEngine->getConversationStore();

        $conversationModel = $conversationStore->getEIModelConversationTemplate('hello_bot_world');

        $conversationConverter = resolve(EIModelToGraphConverter::class);
        $conversation = $conversationConverter->convertConversation($conversationModel);

        $conditions = $conversation->getConditions();

        $this->assertCount(2, $conditions);

        /* @var Condition $condition */
        foreach ($conditions as $condition) {
            if ($condition->getId() === 'user.name-is_set-') {
                $this->assertTrue($condition->getEvaluationOperation() == IsSetOperation::$name);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == IsSetOperation::$name);
            }

            if ($condition->getId() === 'user.test-gt-10') {
                $this->assertTrue($condition->getEvaluationOperation() == GreaterThanOperation::$name);
                $this->assertTrue($condition->getAttribute(Model::OPERATION)->getValue() == GreaterThanOperation::$name);
            }
        }
    }

    /**
     * @throws FieldNotSupported
     */
    public function testConversationEngineNoOngoingConversation()
    {
        $userContext = $this->createUserContext();
        $this->assertEquals($this->utterance->getUserId(), $userContext->getUserId());
        $this->assertFalse($userContext->isUserHavingConversation());
    }

    /**
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws BindingResolutionException
     * @throws EIModelCreatorException
     * @throws NodeDoesNotExistException
     */
    public function testConversationEngineOngoingConversation()
    {
        /* @var UserContext $userContext; */
        $userContext = $this->createConversationAndAttachToUser();
        $this->assertEquals($this->utterance->getUserId(), $userContext->getUserId());
        $this->assertTrue($userContext->isUserHavingConversation());
    }

    /**
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws EIModelCreatorException
     * @throws CurrentIntentNotSetException
     */
    public function testDeterminingCurrentConversationWithoutOngoingConversation()
    {
        $userContext = $this->createUserContext();

        $conversation = $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);
        $this->assertEquals('no_match_conversation', $conversation->getId());
        $this->assertEquals('no_match_conversation', $userContext->getCurrentConversation()->getId());
    }

    /**
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     */
    public function testDeterminingNextIntentWithoutOngoingConversation()
    {
        // This is setup to match the NoMatch conversation

        $userContext = $this->createUserContext();

        $intent = $this->conversationEngine->getNextIntent($userContext, $this->utterance);
        $this->assertEquals('intent.core.NoMatchResponse', $intent->getId());

        $this->assertFalse($userContext->isUserHavingConversation());
    }

    /**
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws EIModelCreatorException
     * @throws CurrentIntentNotSetException
     */
    public function testDeterminingNextIntentsInMultiSceneConversation()
    {
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new IntAttribute('test', 11));

        $this->utterance->setCallbackId('hello_bot');
        /* @var InterpreterServiceInterface $interpreterService */
        $interpreterService = resolve(InterpreterServiceInterface::class);
        /* @var CallbackInterpreter $callbackInterpeter */
        $callbackInterpeter = $interpreterService->getDefaultInterpreter();
        $callbackInterpeter->addCallback('hello_bot', 'hello_bot');
        $callbackInterpeter->addCallback('how_are_you', 'how_are_you');
        $callbackInterpeter->addCallback('hello_registered_user', 'hello_registered_user');

        // Let's see if we get the right next intent for the first step.
        $intent = $this->conversationEngine->getNextIntent($userContext, $this->utterance);
        $validIntents = ['hello_user','hello_registered_user'];
        $this->assertContains($intent->getId(), $validIntents);

        $this->assertContains($userContext->getCurrentIntent()->getId(), $validIntents);

        // Ok, now the conversation has moved on let us take the next step
        /* @var WebchatChatOpenUtterance $nextUtterance */
        $nextUtterance = new WebchatChatOpenUtterance();
        if ($intent->getId() === 'hello_user') {
            $nextUtterance->setCallbackId('how_are_you');
            $intent = $this->conversationEngine->getNextIntent($userContext, $nextUtterance);
            $this->assertEquals('doing_dandy', $intent->getId());
        }
        if ($intent->getId() === 'hello_registered_user') {
            $nextUtterance->setCallbackId('weather_question');
            $intent = $this->conversationEngine->getNextIntent($userContext, $nextUtterance);
            $this->assertEquals('intent.core.NoMatchResponse', $intent->getId());
        }
    }

    /**
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws BindingResolutionException
     * @throws EIModelCreatorException
     * @throws NodeDoesNotExistException
     * @throws CurrentIntentNotSetException
     */
    public function testDeterminingCurrentConversationWithOngoingConversation()
    {
        $userContext = $this->createConversationAndAttachToUser();

        $conversation = $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);

        // Ensure that the $conversation is the right one.
        $this->assertEquals($conversation->getId(), 'hello_bot_world');
        $this->assertCount(4, $conversation->getAllScenes());
        $this->assertEquals('opening_scene', $conversation->getScene('opening_scene')->getId());
        $this->assertEquals('scene2', $conversation->getScene('scene2')->getId());

        $openingScene = $conversation->getScene('opening_scene');
        $this->assertCount(1, $openingScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $openingScene->getIntentsSaidByUser()->get('hello_bot');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertEquals($userIntent->getAction()->getId(), 'action.core.example');
        $this->assertEquals($userIntent->getInterpreter()->getId(), 'interpreter.core.callbackInterpreter');

        $this->assertCount(2, $openingScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $openingScene->getIntentsSaidByBot()->get('hello_user');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertEquals('action.core.example', $botIntent->getAction()->getId());

        $secondScene = $conversation->getScene('scene2');

        $this->assertCount(1, $secondScene->getIntentsSaidByUser());
        /* @var Intent $userIntent */
        $userIntent = $secondScene->getIntentsSaidByUser()->get('how_are_you');
        $this->assertTrue($userIntent->hasInterpreter());
        $this->assertTrue($userIntent->causesAction());
        $this->assertEquals('action.core.example', $userIntent->getAction()->getId());
        $this->assertEquals('interpreter.core.callbackInterpreter', $userIntent->getInterpreter()->getId());

        $this->assertCount(1, $secondScene->getIntentsSaidByBot());
        /* @var Intent $botIntent */
        $botIntent = $secondScene->getIntentsSaidByBot()->get('doing_dandy');
        $this->assertFalse($botIntent->hasInterpreter());
        $this->assertTrue($botIntent->causesAction());
        $this->assertEquals('action.core.example', $botIntent->getAction()->getId());
    }

    /**
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     */
    public function testPerformIntentAction()
    {
        $interpreterService = resolve(InterpreterServiceInterface::class);
        $callbackInterpreter = $interpreterService->getDefaultInterpreter();
        $callbackInterpreter->addCallback('hello_bot', 'hello_bot');

        $userContext = $this->createUserContext();

        $this->activateConversation($this->conversationWithNonBindedAction());

        try {
            $this->conversationEngine->determineCurrentConversation($userContext, $this->utterance);
        } catch (Exception $e) {
            $this->fail('No exception should be thrown when calling an unbound action.');
        }

        try {
            $userContext->getAttribute('full_name');
            $this->fail('Attribute full_name should not exist.');
        } catch (AttributeDoesNotExistException $e) {
        }

        $this->conversationEngine->getNextIntent($userContext, $this->utterance);

        $fullName = $userContext->getAttribute('full_name')->getValue();
        $this->assertTrue($fullName !== '');
    }

    /**
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     */
    public function testCallbackIdNotMappedToIntent()
    {
        $userContext = $this->createUserContext();
        $userContext->addAttribute(new IntAttribute('test', 11));

        $utterance = UtteranceGenerator::generateButtonResponseUtterance('howdy_bot');
        /* @var InterpreterServiceInterface $interpreterService */
        $interpreterService = resolve(InterpreterServiceInterface::class);
        /* @var CallbackInterpreter $callbackInterpeter */
        $callbackInterpeter = $interpreterService->getDefaultInterpreter();
        $callbackInterpeter->addCallback('hello_bot', 'hello_bot');
        $callbackInterpeter->addCallback('how_are_you', 'how_are_you');
        $callbackInterpeter->addCallback('hello_registered_user', 'hello_registered_user');

        // Let's see if we get the right next intent for the first step.
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);
        $this->assertEquals('hello_user', $intent->getId());
    }

    public function testGetLatestVersion()
    {
        $this->createUpdates('hello_bot_world');

        /** @var ConversationStoreInterface $conversationStore */
        $conversationStore = $this->conversationEngine->getConversationStore();

        // Test that we can query a conversation with history using the ConversationStore
        /** @var EIModelConversation $conversationWithHistory */
        $conversationWithHistory = $conversationStore->getLatestEIModelTemplateVersionByName('hello_bot_world');
        $this->assertEquals(2, $conversationWithHistory->getConversationVersion());
    }

    public function testGetHistory()
    {
        $this->createUpdates('hello_bot_world');

        $conversation = Conversation::where('name', 'hello_bot_world')->first();

        $this->assertCount(3, $conversation->history);
    }

    public function testSceneConditions()
    {
        $this->activateConversation($this->conversationWithSceneConditions());

        // No scene conditions pass with valid opening scene intent, expect to match opening scene intent
        $utterance = UtteranceGenerator::generateChatOpenUtterance('opening_user_none');
        $userContext = ContextService::createUserContext($utterance);
        $userContext->addAttribute(AttributeResolverFacade::getAttributeFor('user_email', 'test@example.com'));
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('opening_bot_response', $intent->getId());

        // Progress conversation, expect to not enter scene3
        $utterance = UtteranceGenerator::generateChatOpenUtterance('opening_user_s3', $utterance->getUser());
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('intent.core.NoMatchResponse', $intent->getId());


        // Restart conversation: Both scene conditions pass-able, expect first to be matched
        $utterance = UtteranceGenerator::generateChatOpenUtterance('opening_user_s1');
        $userContext = ContextService::createUserContext($utterance);
        $userContext->addAttribute(AttributeResolverFacade::getAttributeFor('user_name', 'test_user'));
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('scene1_bot', $intent->getId());

        // Restart conversation: First scene conditions fails, second passes, expect second to be matched
        $utterance = UtteranceGenerator::generateChatOpenUtterance('opening_user_s2');
        $userContext = ContextService::createUserContext($utterance);
        $userContext->addAttribute(AttributeResolverFacade::getAttributeFor('user_name', 'test_user'));
        $userContext->addAttribute(AttributeResolverFacade::getAttributeFor('user_email', 'test@example.com'));
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('scene2_bot', $intent->getId());

        // Restart conversation: No scene conditions pass with invalid opening scene intent, expect a no-match
        $utterance = UtteranceGenerator::generateChatOpenUtterance('opening_user_doesnt_exist');
        $userContext = ContextService::createUserContext($utterance);
        $userContext->addAttribute(AttributeResolverFacade::getAttributeFor('user_email', 'test@example.com'));
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('intent.core.NoMatchResponse', $intent->getId());

        // Restart conversation: No scene conditions pass with valid opening scene intent, expect a no-match
        $utterance = UtteranceGenerator::generateChatOpenUtterance('opening_user_s2');
        $userContext = ContextService::createUserContext($utterance);
        $userContext->addAttribute(AttributeResolverFacade::getAttributeFor('user_email', 'test@example.com'));
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('intent.core.NoMatchResponse', $intent->getId());
    }

    /**
     * @return UserContext
     */
    private function createUserContext()
    {
        $userContext = ContextService::createUserContext($this->utterance);

        return $userContext;
    }

    /**
     * @return UserContext
     * @throws GuzzleException
     * @throws BindingResolutionException
     * @throws EIModelCreatorException
     */
    private function createConversationAndAttachToUser()
    {
        $conversationStore = resolve(ConversationStoreInterface::class);

        $this->assertFalse($conversationStore->hasConversationBeenUsed('hello_bot_world'));

        $conversationConverter = resolve(EIModelToGraphConverter::class);

        $conversationModel = $conversationStore->getEIModelConversationTemplate('hello_bot_world');

        /** @var \OpenDialogAi\Core\Conversation\Conversation $conversationTemplate */
        $conversationTemplateForCloning = $conversationConverter->convertConversation($conversationModel, true);
        $conversationTemplateForConnecting = $conversationConverter->convertConversation($conversationModel, false);

        /* @var UserContext $userContext */
        $userContext = $this->createUserContext();
        $user = $userContext->getUser();

        $user->setCurrentConversation($conversationTemplateForCloning, $conversationTemplateForConnecting);
        $userContext->updateUser();

        $this->assertTrue($conversationStore->hasConversationBeenUsed('hello_bot_world'));

        $conversationModel = $conversationStore->getEIModelConversation($userContext->getUser()->getCurrentConversationUid());

        /** @var \OpenDialogAi\Core\Conversation\Conversation $conversation */
        $conversation = $conversationConverter->convertConversation($conversationModel);

        $this->assertTrue($conversation->hasInstanceOf());

        /** @var \OpenDialogAi\Core\Conversation\Conversation $instanceOf */
        $instanceOf = $conversation->getInstanceOf();

        $this->assertEquals($conversation->getId(), $instanceOf->getId());
        $this->assertNotEquals(
            $conversation->getAttributeValue(Model::EI_TYPE),
            $instanceOf->getAttributeValue(Model::EI_TYPE)
        );

        $this->assertEquals($userContext->getUser()->getCurrentConversationUid(), $conversation->getUid());

        $this->assertFalse($conversation->hasUpdateOf());

        /* @var Scene $scene */
        $scene = $conversation->getOpeningScenes()->first()->value;
        $intent = $scene->getIntentByOrder(1);

        $userContext->setCurrentIntent($intent);
        $userContext->updateUser();

        return $userContext;
    }

    /**
     * @return string
     */
    private function conversationWithNonBindedAction()
    {
        return <<<EOT
conversation:
  id: non_binded
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
            action: action.test.not_bound
        - b: 
            i: hello_user
            action:
              id: action.core.example
              input_attributes:
                - user.first_name
                - user.last_name
            completes: true
EOT;
    }

    /**
     * @param string $templateName
     * @throws BindingResolutionException
     */
    private function createUpdates(string $templateName)
    {
        /** @var Conversation $template */
        $template = Conversation::where('name', $templateName)->first();
        $template->model .= " ";
        $template->activateConversation();

        $template = Conversation::where('name', $templateName)->first();
        $template->model .= " ";
        $template->activateConversation();
    }
}
