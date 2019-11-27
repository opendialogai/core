<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class ConversationContextTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testConversationalContextNewUser()
    {
        $this->activateConversation($this->conversation4());

        $utterance = UtteranceGenerator::generateTextUtterance('hello');

        resolve(OpenDialogController::class)->runConversation($utterance);

        $conversationContext = ContextService::getConversationContext();

        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'intent.core.NoMatch');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'no_match_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intents')[0], 'intent.core.NoMatchResponse');
    }

    public function testConversationalContextOnGoingUser()
    {
        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot',
            'how_are_you' => 'how_are_you',
            'good_to_hear' => 'good_to_hear'
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->activateConversation($this->getTestConversation());

        $conversationContext = ContextService::getConversationContext();

        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
        $openDialogController->runConversation($utterance);

        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'hello_bot');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'opening_scene');
        $this->assertEquals($conversationContext->getAttributeValue('next_intents')[0], 'hello_user');

        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('how_are_you', $utterance->getUser()));

        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'how_are_you');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'scene2');
        $this->assertEquals($conversationContext->getAttributeValue('current_intent'), 'hello_user');
        $this->assertEquals($conversationContext->getAttributeValue('next_intents')[0], 'doing_dandy');

        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('good_to_hear', $utterance->getUser()));

        $this->assertEquals($conversationContext->getAttributeValue('interpreted_intent'), 'good_to_hear');
        $this->assertEquals($conversationContext->getAttributeValue('current_conversation'), 'test_conversation');
        $this->assertEquals($conversationContext->getAttributeValue('current_scene'), 'scene3');
        $this->assertEquals($conversationContext->getAttributeValue('current_intent'), 'doing_dandy');
        $this->assertEquals($conversationContext->getAttributeValue('next_intents')[0], 'ok_bye');
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
            scene: scene2
    scene2:
      intents:
        - u: 
            i: how_are_you
        - b: 
            i: doing_dandy
            scene: scene3
    scene3:
      intents:
        - u:
            i: good_to_hear
        - b:
            i: ok_bye
            completes: true
EOT;
    }
}
