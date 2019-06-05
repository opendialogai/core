<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ConversationLogTest extends TestCase
{
    /**
     * Test that a Chatbot User can be created.
     *
     * @return void
     */
    public function testChatbotUserCanBeCreated()
    {
        ChatbotUser::create([
            'user_id' => 'test@example.com',
            'first_name' => 'Joe',
            'last_name' => 'Cool',
            'ip_address' => '127.0.0.1',
            'country' => 'UK',
            'browser_language' => 'en',
            'os' => 'Mac OS X',
            'browser' => 'Safari',
            'timezone' => 'GMT',
            'platform' => 'webchat',
        ]);
        $chatbotUser = ChatbotUser::where('user_id', 'test@example.com')->first();
        $this->assertEquals('webchat', $chatbotUser->platform);
    }

    /**
     * Ensure that the ChatbotUser/message relationships work correctly.
     */
    public function testChatbotUserDbRelationships()
    {
        ChatbotUser::create([
            'user_id' => 'test@example.com',
            'first_name' => 'Joe',
            'last_name' => 'Cool',
            'ip_address' => '127.0.0.1',
            'country' => 'UK',
            'browser_language' => 'en',
            'os' => 'Mac OS X',
            'browser' => 'Safari',
            'timezone' => 'GMT',
        ]);
        $chatbotUser = ChatbotUser::where('user_id', 'test@example.com')->first();

        $message = Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'test message');
        $message->save();

        // Ensure we can get a Message's ChatbotUser.
        $this->assertEquals($chatbotUser->user_id, $message->chatbotUser->user_id);

        // Ensure we can get a ChatbotUser's Messages.
        $this->assertTrue($chatbotUser->messages->contains($message));
    }

    /**
     * Ensure that messages can be retrieved from the webchat history endpoint.
     */
    public function testWebchatHistoryEndpoint()
    {
        ChatbotUser::create([
            'user_id' => 'test@example.com',
            'first_name' => 'Joe',
            'last_name' => 'Cool',
            'ip_address' => '127.0.0.1',
            'country' => 'UK',
            'browser_language' => 'en',
            'os' => 'Mac OS X',
            'browser' => 'Safari',
            'timezone' => 'GMT',
        ]);
        $chatbotUser = ChatbotUser::where('user_id', 'test@example.com')->first();

        Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'test message')->save();
        Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'another test message')->save();

        $this->get('/user/test@example.com/history?limit=5')
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['user_id' => 'test@example.com']
            ]);
    }

    /**
     * Ensure that the webchat history endpoint ignore param works.
     */
    public function testWebchatHistoryEndpointIgnoreParam()
    {
        ChatbotUser::create([
            'user_id' => 'test@example.com',
            'first_name' => 'Joe',
            'last_name' => 'Cool',
            'ip_address' => '127.0.0.1',
            'country' => 'UK',
            'browser_language' => 'en',
            'os' => 'Mac OS X',
            'browser' => 'Safari',
            'timezone' => 'GMT',
        ]);
        $chatbotUser = ChatbotUser::where('user_id', 'test@example.com')->first();

        Message::create(microtime(), 'chat_open', $chatbotUser->user_id, 'me', '')->save();
        Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'test message')->save();
        Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'another test message')->save();
        Message::create(microtime(), 'trigger', $chatbotUser->user_id, 'me', '')->save();

        $this->get('/user/test@example.com/history?limit=10&ignore=chat_open,trigger')
            ->assertStatus(200)
            ->assertJsonCount(2);

        $this->get('/user/test@example.com/history?limit=10')
            ->assertStatus(200)
            ->assertJsonCount(4);
    }

    /**
     * Ensure that the webchat history endpoint message limit works.
     */
    public function testWebchatHistoryEndpointLimit()
    {
        ChatbotUser::create([
            'user_id' => 'test@example.com',
            'first_name' => 'Joe',
            'last_name' => 'Cool',
            'ip_address' => '127.0.0.1',
            'country' => 'UK',
            'browser_language' => 'en',
            'os' => 'Mac OS X',
            'browser' => 'Safari',
            'timezone' => 'GMT',
        ]);
        $chatbotUser = ChatbotUser::where('user_id', 'test@example.com')->first();

        Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'test message')->save();
        Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'another test message')->save();

        $this->get('/user/test@example.com/history?limit=1')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJson([
                ['user_id' => 'test@example.com']
            ]);
    }

    /**
     * Test that incoming & outgoing messages are logged.
     */
    public function testMessageLogging()
    {
        // Test a valid message.
        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'text',
                'data' => [
                    'text' => 'test message',
                ],
                'user' => [
                    'ipAddress' => '127.0.0.1',
                    'country' => 'UK',
                    'browserLanguage' => 'en-gb',
                    'os' => 'macos',
                    'browser' => 'safari',
                    'timezone' => 'GMT',
                ],
            ],
        ]);
        $response
            ->assertStatus(200)
            ->assertJson([0 => ['data' => ['text' => 'No messages found for intent intent.core.NoMatchResponse']]]);

        // Ensure that incoming messages are logged.
        $this->assertDatabaseHas('messages', [
            'user_id' => 'someuser',
            'message' => 'test message',
        ]);

        // Ensure that outgoing messages are logged.
        $this->assertDatabaseHas('messages', [
            'author' => 'them',
            'message' => 'No messages found for intent intent.core.NoMatchResponse',
        ]);

        // Ensure that the correct history is returned
        $this->get('/user/someuser/history?limit=2')
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['user_id' => 'someuser']
            ]);
    }

    public function testInternalProperty()
    {
        $this->initDDgraph();

        $validCallback = ['welcome' => 'intent.core.welcome'];
        $this->setConfigValue('opendialog.interpreter_engine.supported_callbacks', $validCallback);

        $intent = OutgoingIntent::create(['name' => 'intent.core.chat_open_response']);

        $messageMarkUp = new MessageMarkUpGenerator();
        $messageMarkUp->addTextMessage('Message 1');
        $messageMarkUp->addTextMessage('Message 2');
        $messageMarkUp->addTextMessage('Message 3');

        MessageTemplate::create([
            'name' => 'Friendly Hello',
            'outgoing_intent_id' => $intent->id,
            'conditions' => '',
            'message_markup' => $messageMarkUp->getMarkUp(),
        ]);
        $messageTemplate = MessageTemplate::where('name', 'Friendly Hello')->first();

        $cm = new ConversationManager('TestConversation');

        $conversationModel = Conversation::create(['name' => 'chat_open', 'model' => "conversation:\n id: chat_open\n scenes:\n    opening_scene:\n      intents:\n        - u:\n            i: intent.core.welcome\n        - b:\n            i: intent.core.chat_open_response\n            completes: true"]);

        $conversationModel->publishConversation($conversationModel->buildConversation());

        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'chat_open',
                'data' => [
                    'callback_id' => 'welcome',
                ],
                'user' => [
                    'ipAddress' => '127.0.0.1',
                    'country' => 'UK',
                    'browserLanguage' => 'en-gb',
                    'os' => 'macos',
                    'browser' => 'safari',
                    'timezone' => 'GMT',
                ],
            ],
        ]);
        $response
            ->assertStatus(200)
            ->assertJson([0 => ['data' => ['text' => 'Message 1']]])
            ->assertJson([0 => ['data' => ['internal' => false]]])
            ->assertJson([0 => ['data' => ['hidetime' => true]]])
            ->assertJson([1 => ['data' => ['text' => 'Message 2']]])
            ->assertJson([1 => ['data' => ['internal' => true]]])
            ->assertJson([1 => ['data' => ['hidetime' => true]]])
            ->assertJson([2 => ['data' => ['text' => 'Message 3']]])
            ->assertJson([2 => ['data' => ['internal' => true]]])
            ->assertJson([2 => ['data' => ['hidetime' => false]]]);

        $response = $this->get('/user/someuser/history?limit=10&ignore=chat_open');
        $response
            ->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJson([2 => ['data' => ['text' => 'Message 1']]])
            ->assertJson([2 => ['data' => ['internal' => false]]])
            ->assertJson([2 => ['data' => ['hidetime' => true]]])
            ->assertJson([1 => ['data' => ['text' => 'Message 2']]])
            ->assertJson([1 => ['data' => ['internal' => true]]])
            ->assertJson([1 => ['data' => ['hidetime' => true]]])
            ->assertJson([0 => ['data' => ['text' => 'Message 3']]])
            ->assertJson([0 => ['data' => ['internal' => true]]])
            ->assertJson([0 => ['data' => ['hidetime' => false]]]);
    }
}
