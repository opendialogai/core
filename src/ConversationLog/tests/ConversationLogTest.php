<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Tests\TestCase;

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
     * Ensure that messages can be retrieved from the webchat chat-init endpoint.
     */
    public function testWebchatChatInitEndpoint()
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

        $message = Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'test message')->save();
        $message2 = Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'another test message')->save();

        $response = $this->get('/chat-init/webchat/test@example.com/5')
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['user_id' => 'test@example.com']
            ]);
    }

    /**
     * Ensure that the webchat chat-init endpoint message limit works.
     */
    public function testWebchatChatInitEndpointLimit()
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

        $message = Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'test message')->save();
        $message2 = Message::create(microtime(), 'text', $chatbotUser->user_id, 'me', 'another test message')->save();

        $response = $this->get('/chat-init/webchat/test@example.com/1')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJson([
                ['user_id' => 'test@example.com']
            ]);
    }
}
