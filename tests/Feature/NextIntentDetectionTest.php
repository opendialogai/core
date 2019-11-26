<?php

namespace OpenDialogAi\Core\Tests\Feature;

use Exception;
use Mockery\MockInterface;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\Core\Attribute\StringAttribute;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class NextIntentDetectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
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
        $this->assertEquals('hello_user', $conversationContext->getAttributeValue('next_intents')[0]);

        // Ask about the weather - this should keep the user in the opening scene
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('ask_weather', $utterance->getUser()));
        $this->assertEquals('ask_weather', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('send_weather', $conversationContext->getAttributeValue('next_intents')[0]);

        // Respond to the weather
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('respond_weather', $utterance->getUser()));
        $this->assertEquals('respond_weather', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('finish', $conversationContext->getAttributeValue('next_intents')[0]);
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
        $this->assertEquals('start_chat', $conversationContext->getAttributeValue('next_intents')[0]);

        // Respond to the weather
        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('how_are_you', $utterance->getUser()));
        $this->assertEquals('how_are_you', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('test_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('scene2', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('doing_dandy', $conversationContext->getAttributeValue('next_intents')[0]);
    }

    public function testConditionsOnOutgoingIntents()
    {
        $this->setCustomAttributes([
            'user_name' => StringAttribute::class
        ]);

        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot'
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->activateConversation($this->conversationWithOutgoingIntentConditions());

        $conversationContext = ContextService::getConversationContext();

        // Expect to get to 'response_without_name' because the user name isn't set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('hello_bot', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_outgoing_intent_conditions', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('response_without_name', $conversationContext->getAttributeValue('next_intents')[0]);

        // Expect to get to 'answer_without_name' because the user name isn't set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('question', $utterance->getUser());
        $openDialogController->runConversation($utterance);
        $this->assertEquals('question', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_outgoing_intent_conditions', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('answer_without_name', $conversationContext->getAttributeValue('next_intents')[0]);

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
        $this->assertEquals('response_with_name', $conversationContext->getAttributeValue('next_intents')[0]);

        // Expect to get to 'answer_with_name' because the user name is set
        $utterance = UtteranceGenerator::generateChatOpenUtterance('question', $utterance->getUser());
        $openDialogController->runConversation($utterance);
        $this->assertEquals('question', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_outgoing_intent_conditions', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('answer_with_name', $conversationContext->getAttributeValue('next_intents')[0]);
    }

    public function testConversationWithOpeningIncomingIntentConditions()
    {
        $this->setCustomAttributes([
            'user_email' => StringAttribute::class
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $conversationMarkup =
            /** @lang yaml */
            <<<EOT
conversation:
  id: my_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.app.hello
            conditions:
                - condition:
                    operation: is_set
                    attributes:
                        attribute1: user.email
        - b:
            i: intent.app.response
            completes: true
EOT;

        try {
            $this->activateConversation($conversationMarkup);
            $this->activateConversation($this->noMatchConversation());
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $conversationContext = ContextService::getConversationContext();

        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.hello');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.core.NoMatch', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('no_match_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.core.NoMatchResponse', $conversationContext->getAttributeValue('next_intents')[0]);

        // Set the email and expect to get to the response intent
        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.hello');
        $utterance->getUser()->setCustomParameters([
            'email' => 'test@example.com'
        ]);
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.app.hello', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('my_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.response', $conversationContext->getAttributeValue('next_intents')[0]);

    }

    public function testMultiSceneConversationWithOpeningIncomingIntentConditions()
    {
        $this->setCustomAttributes([
            'email' => StringAttribute::class
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $conversationMarkup =
            /** @lang yaml */
            <<<EOT
conversation:
  id: my_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.app.hello
            conditions:
                - condition:
                    operation: is_not_set
                    attributes:
                        attribute1: user.email
            scene: get_email
        - u: intent.app.hello
        - b:
            i: intent.app.response
            completes: true
    get_email:
      intents:
        - b: intent.app.ask_email
        - u: intent.app.send_email
        - b:
            i: intent.app.response
            completes: true
EOT;

        try {
            $this->activateConversation($conversationMarkup);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $conversationContext = ContextService::getConversationContext();

        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.hello');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.app.hello', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('my_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.ask_email', $conversationContext->getAttributeValue('next_intents')[0]);

        // Set the email and expect to get to the response intent
        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.hello');
        $utterance->getUser()->setCustomParameters([
            'email' => 'test@example.com'
        ]);
        $openDialogController->runConversation($utterance);

        $this->assertEquals('intent.app.hello', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('my_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.response', $conversationContext->getAttributeValue('next_intents')[0]);

    }

    public function testConversationWithManyIntentsWithSameIdAndIncomingConditions()
    {
        $this->setCustomAttributes([
            'user_choice' => StringAttribute::class,
            'game_result' => StringAttribute::class
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $this->createConversationWithManyIntentsWithSameId();

        $conversationContext = ContextService::getConversationContext();

        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.play_game');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.app.play_game', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('rock_paper_scissors', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.init_game', $conversationContext->getAttributeValue('next_intents')[0]);

        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('intent.app.send_choice', $utterance->getUser()));
        $this->assertEquals('intent.app.send_choice', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('rock_paper_scissors', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.round_2', $conversationContext->getAttributeValue('next_intents')[0]);

        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('intent.app.send_choice', $utterance->getUser()));
        $this->assertEquals('intent.app.send_choice', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('rock_paper_scissors', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.final_round', $conversationContext->getAttributeValue('next_intents')[0]);

        // Simulate a bot win
        $utterance->getUser()->setCustomParameters([
            'game_result' => 'BOT_WINS'
        ]);

        $openDialogController->runConversation(UtteranceGenerator::generateChatOpenUtterance('intent.app.send_choice', $utterance->getUser()));
        $this->assertEquals('intent.app.send_choice', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('rock_paper_scissors', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.you_lost', $conversationContext->getAttributeValue('next_intents')[0]);
    }

    public function testConversationWithIncomingConditions()
    {
        $this->setSupportedCallbacks([
            'make_choice' => 'intent.app.make_choice'
        ]);

        $this->setCustomAttributes([
            'choice' => StringAttribute::class
        ]);

        $openDialogController = resolve(OpenDialogController::class);

        $conversationMarkup =
            /** @lang yaml */
            <<<EOT
conversation:
  id: my_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: intent.app.hello
        - b:
            i: intent.app.response
        - u:
            i: intent.app.make_choice
            expected_attributes:
              - id: user.choice
            conditions:
                - condition:
                    operation: eq
                    attributes:
                        attribute1: _intent.choice
                    parameters:
                        value: 'left'
            scene: left_path
        - u:
            i: intent.app.make_choice
            expected_attributes:
              - id: user.choice
        - b:
            i: intent.app.right_path_end
            completes: true
    left_path:
      intents:
        - b:
            i: intent.app.left_path_end
            completes: true
EOT;

        try {
            $this->activateConversation($conversationMarkup);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $conversationContext = ContextService::getConversationContext();

        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.hello');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.app.hello', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('my_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.response', $conversationContext->getAttributeValue('next_intents')[0]);

        $utterance = UtteranceGenerator::generateButtonResponseUtterance('make_choice', 'choice.left', $utterance->getUser());
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.app.make_choice', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('my_conversation', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertEquals('intent.app.left_path_end', $conversationContext->getAttributeValue('next_intents')[0]);
    }

    public function testMultipleNextIntents()
    {
        $test1 = 'This is Test1.';
        $test1Intent = OutgoingIntent::create(['name' => 'test1']);
        MessageTemplate::create([
            'name' => 'test1',
            'message_markup' => '<message><text-message>' . $test1 . '</text-message></message>',
            'outgoing_intent_id' => $test1Intent->id
        ]);

        $test2_1 = 'This is Test2 (part 1).';
        $test2_2 = 'This is Test2 (part 2).';
        $test2Intent = OutgoingIntent::create(['name' => 'test2']);
        MessageTemplate::create([
            'name' => 'test2',
            'message_markup' => '
                <message>
                    <text-message>' . $test2_1 . '</text-message>
                    <text-message>' . $test2_2 . '</text-message>
                </message>',
            'outgoing_intent_id' => $test2Intent->id
        ]);

        $mockedConversationEngine = $this->mock(ConversationEngine::class, function (MockInterface $mock) {
            $mock->shouldReceive('getNextIntents')->andReturn([
                Intent::createIntentWithConfidence('test1', 1),
                Intent::createIntentWithConfidence('test2', 1)
            ]);
        });

        $openDialogController = resolve(OpenDialogController::class);
        $openDialogController->setConversationEngine($mockedConversationEngine);

        $utterance = UtteranceGenerator::generateChatOpenUtterance('test');
        $messages = $openDialogController->runConversation($utterance)->getMessages();

        $this->assertCount(3, $messages);
        $this->assertEquals($test1, $messages[0]->getText());
        $this->assertEquals($test2_1, $messages[1]->getText());
        $this->assertEquals($test2_2, $messages[2]->getText());
    }

    public function testSingleVirtualIntents()
    {
        $openDialogController = resolve(OpenDialogController::class);

        $this->createConversationWithVirtualIntent();

        $conversationContext = ContextService::getConversationContext();

        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.welcome');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.app.welcome', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_virtual_intent', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('opening_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertCount(2, $conversationContext->getAttributeValue('next_intents'));
        $this->assertEquals('intent.app.welcomeResponse', $conversationContext->getAttributeValue('next_intents')[0]);
        $this->assertEquals('intent.app.endResponse', $conversationContext->getAttributeValue('next_intents')[1]);
    }

    public function testMultipleVirtualIntents()
    {
        $openDialogController = resolve(OpenDialogController::class);

        $this->createConversationWithMultipleVirtualIntents();

        $conversationContext = ContextService::getConversationContext();

        $utterance = UtteranceGenerator::generateChatOpenUtterance('intent.app.welcome');
        $openDialogController->runConversation($utterance);
        $this->assertEquals('intent.app.welcome', $conversationContext->getAttributeValue('interpreted_intent'));
        $this->assertEquals('with_virtual_intents', $conversationContext->getAttributeValue('current_conversation'));
        $this->assertEquals('next_scene', $conversationContext->getAttributeValue('current_scene'));
        $this->assertCount(3, $conversationContext->getAttributeValue('next_intents'));
        $this->assertEquals('intent.app.welcomeResponse', $conversationContext->getAttributeValue('next_intents')[0]);
        $this->assertEquals('intent.app.continueResponse', $conversationContext->getAttributeValue('next_intents')[1]);
        $this->assertEquals('intent.app.nextResponse', $conversationContext->getAttributeValue('next_intents')[2]);
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

    public function conversationWithOutgoingIntentConditions()
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
