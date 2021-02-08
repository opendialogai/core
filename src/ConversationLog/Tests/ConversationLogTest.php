<?php

namespace OpenDialogAi\ConversationLog\Tests;

use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class ConversationLogTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

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
     * @requires DGRAPH
     *
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
     * @requires DGRAPH
     *
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
            ->assertJson(['data' => ['text' => 'No messages found for intent intent.core.NoMatchResponse']]);

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

    /**
     * @requires DGRAPH
     */
    public function testInternalProperty()
    {
        $validCallback = ['welcome' => 'intent.core.welcome'];
        $this->setSupportedCallbacks($validCallback);

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

        $response = $this->json('POST', '/incoming/webchat', [
            'notification' => 'message',
            'user_id' => 'someuser',
            'author' => 'me',
            'content' => [
                'author' => 'me',
                'type' => 'chat_open',
                'callback_id' => 'welcome',
                'data' => [],
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
            ->assertJson([0 => ['intent' => 'intent.core.chat_open_response']])
            ->assertJson([0 => ['data' => ['text' => 'Message 1']]])
            ->assertJson([0 => ['data' => ['internal' => true]]])
            ->assertJson([0 => ['data' => ['hidetime' => true]]])
            ->assertJson([1 => ['intent' => 'intent.core.chat_open_response']])
            ->assertJson([1 => ['data' => ['text' => 'Message 2']]])
            ->assertJson([1 => ['data' => ['internal' => true]]])
            ->assertJson([1 => ['data' => ['hidetime' => true]]])
            ->assertJson([2 => ['intent' => 'intent.core.chat_open_response']])
            ->assertJson([2 => ['data' => ['text' => 'Message 3']]])
            ->assertJson([2 => ['data' => ['internal' => false]]])
            ->assertJson([2 => ['data' => ['hidetime' => false]]]);

        $response = $this->get('/user/someuser/history?limit=10&ignore=chat_open');
        $response
            ->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJson([2 => ['data' => ['text' => 'Message 1']]])
            ->assertJson([2 => ['data' => ['internal' => true]]])
            ->assertJson([2 => ['data' => ['hidetime' => true]]])
            ->assertJson([1 => ['data' => ['text' => 'Message 2']]])
            ->assertJson([1 => ['data' => ['internal' => true]]])
            ->assertJson([1 => ['data' => ['hidetime' => true]]])
            ->assertJson([0 => ['data' => ['text' => 'Message 3']]])
            ->assertJson([0 => ['data' => ['internal' => false]]])
            ->assertJson([0 => ['data' => ['hidetime' => false]]]);
    }

    public function testChatbotUserFirstLastSeen()
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

        $chatbotUser = ChatbotUser::where('user_id', 'test@example.com')->first();
        $message = $chatbotUser->messages()->first();
        $message->created_at = date('Y-m-d H:i:s', time() + 600);
        $message->save();

        $this->assertEquals($chatbotUser->first_seen, $chatbotUser->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($chatbotUser->last_seen, $message->created_at);
    }
}
