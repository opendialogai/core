<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\AttributeResolver;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class NextIntentDetectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        AttributeResolver::registerAttributes([
            'user_name' => StringAttribute::class
        ]);

        $this->activateConversation($this->conversation4());
    }

    public function testComplexConversation()
    {
        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot',
            'ask_weather' => 'ask_weather',
            'respond_weather' => 'respond_weather'
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->activateConversation($this->getTestConversation());

        $conversationContext = ContextService::getConversationContext();

        // Start the conversation
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('hello_bot', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('hello_user', $conversationContext->getAttributeValue('next_intent'));

        // Ask about the weather - this should keep the user in the opening scene
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('ask_weather', $utterance->getUser()));
        $this->assertEquals('ask_weather', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('send_weather', $conversationContext->getAttributeValue('next_intent'));

        // Respond to the weather
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('respond_weather', $utterance->getUser()));
        $this->assertEquals('respond_weather', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('finish', $conversationContext->getAttributeValue('next_intent'));
    }

    public function testSaidAcrossScene()
    {
        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot',
            'ask_chat' => 'ask_chat',
            'how_are_you' => 'how_are_you',
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->activateConversation($this->getTestConversation());

        $conversationContext = ContextService::getConversationContext();

        // Ask to chat - this should keep the user in the opening scene
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
        $openDialogController->runConversation($utterance);
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('ask_chat', $utterance->getUser()));
        $this->assertEquals('ask_chat', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('start_chat', $conversationContext->getAttributeValue('next_intent'));

        // Respond to the weather
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('how_are_you', $utterance->getUser()));
        $this->assertEquals('how_are_you', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('scene2', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('doing_dandy', $conversationContext->getAttributeValue('next_intent'));
    }

    public function testConditionsOnOutgoingIntents()
    {
        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot'
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->activateConversation($this->testConversationWithOutgoingIntentConditions());

        $conversationContext = ContextService::getConversationContext();

        // Expect to get to 'response_without_name' because the user name isn't set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('hello_bot', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_outgoing_intent_conditions', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('response_without_name', $conversationContext->getAttributeValue('next_intent'));

        // Expect to get to 'answer_without_name' because the user name isn't set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('question', $utterance->getUser());
        $openDialogController->runConversation($utterance);
        $this->assertEquals('question', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_outgoing_intent_conditions', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('answer_without_name', $conversationContext->getAttributeValue('next_intent'));

        // New user
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
        $utterance->getUser()->setCustomParameters([
            "user_name" => "test"
        ]);

        // Expect to get to 'response_with_name' because the user name is set
        $openDialogController->runConversation($utterance);
        $this->assertEquals('hello_bot', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_outgoing_intent_conditions', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('response_with_name', $conversationContext->getAttributeValue('next_intent'));

        // Expect to get to 'answer_with_name' because the user name is set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('question', $utterance->getUser());
        $openDialogController->runConversation($utterance);
        $this->assertEquals('question', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_outgoing_intent_conditions', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('answer_with_name', $conversationContext->getAttributeValue('next_intent'));
    }

    public function getTestConversation()
    {
        return <<<EOT
conversation:
  id: test_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
        - b: 
            i: hello_user
        - u:
            i: ask_weather
        - u: 
            i: ask_chat
            scene: scene2
        - b:
            i: send_weather
        - b:
            i: send_weather2
        - u:
            i: respond_weather
        - b:
            i: finish
            completes: true
    scene2:
      intents:
        - b: 
            i: start_chat
        - u: 
            i: how_are_you
        - b: 
            i: doing_dandy
            completes: true
EOT;
    }

    public function testConversationWithOutgoingIntentConditions()
    {
        return <<<EOT
conversation:
  id: with_outgoing_intent_conditions
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
        - b: 
            i: response_with_name
            conditions:
                - condition:
                    operation: is_set
                    attributes:
                        attribute1: user.user_name
        - b:
            i: response_without_name
            conditions:
                - condition:
                    operation: is_not_set
                    attributes:
                        attribute1: user.user_name
        - u: 
            i: question
        - b: 
            i: answer_with_name
            conditions:
                - condition:
                    operation: is_set
                    attributes:
                        attribute1: user.user_name
            completes: true
        - b:
            i: answer_without_name
            conditions:
                - condition:
                    operation: is_not_set
                    attributes:
                        attribute1: user.user_name
            completes: true
EOT;

    }
}
