<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class NextIntentDetectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
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
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'hello_bot');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'hello_user');

        // Ask about the weather - this should keep the user in the opening scene
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('ask_weather', $utterance->getUser()));
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'ask_weather');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'send_weather');

        // Respond to the weather
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('respond_weather', $utterance->getUser()));
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'respond_weather');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'finish');
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
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'ask_chat');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'start_chat');

        // Respond to the weather
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('how_are_you', $utterance->getUser()));
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'how_are_you');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'scene2');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'doing_dandy');
    }

    public function testConditionsOnOutgoingIntents()
    {
        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot'
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->activateConversation($this->testConversationWithOutgoingIntentConditions());

        $conversationContext = ContextService::getConversationContext();

        // Expect to get to 'response_without_name' because the name isn't set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
        $openDialogController->runConversation($utterance);
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'hello_bot');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'with_outgoing_intent_conditions');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'response_without_name');
    }

    public function testConditionsIncomingIntents()
    {
        $this->setSupportedCallbacks([
            'ask_to_send_me_email' => 'ask_to_send_me_email',
            'send_email' => 'send_email'
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->activateConversation($this->testConversationWithIncomingIntentConditions());

        $conversationContext = ContextService::getConversationContext();

        // Expect to get to 'ask_to_send_me_email' because the email isn't set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('ask_to_send_me_email');
        $openDialogController->runConversation($utterance);
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'ask_to_send_me_email');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'with_incoming_intent_conditions');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'ask_for_email');

        // Expect to get to end of conversation
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('send_email', $utterance->getUser()));
        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'send_email');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'with_incoming_intent_conditions');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'get_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intent'), 'response');
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
                operation: is_set
                attributes:
                    attribute1: user.first_name
        - b:
            i: response_without_name
            conditions:
                operation: is_not_set
                attributes:
                    attribute1: user.first_name
            completes: true
EOT;

    }

    public function testConversationWithIncomingIntentConditions()
    {
        return <<<EOT
conversation:
  id: with_incoming_intent_conditions
  scenes:
      opening_scene:
        intents:
            - u:
                i: ask_to_send_me_email
                conditions:
                    operation: is_set
                    attributes:
                        attribute1: user.email
                scene: send_scene
            - u:
                i: ask_to_send_me_email
                conditions:
                    operation: is_not_set
                    attributes:
                        attribute1: user.email
                scene: get_scene
      get_scene:
        intents:
            - b:
                i: ask_for_email
            - u:
                i: send_email
                scene: send_scene
      send_scene:
        intents:
            - b:
                i: response
                completes: true
EOT;

    }
}
