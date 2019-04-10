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
        //$message = Message::where('message', 'test message')->first();
        // die($message->chatbotUser);

        // Ensure we can get a Message's ChatbotUser.
        $this->assertEquals($chatbotUser->user_id, $message->chatbotUser->user_id);

        // Ensure we can get a ChatbotUser's Messages.
        $this->assertTrue($chatbotUser->messages->contains($message));
    }
}
