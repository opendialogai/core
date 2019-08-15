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
        $this->publishConversation($this->conversation4());
    }

    public function testComplexConversation()
    {
        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot',
            'ask_weather' => 'ask_weather',
            'respond_weather' => 'respond_weather'
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->publishConversation($this->getTestConversation());

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

        $this->publishConversation($this->getTestConversation());

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
}
