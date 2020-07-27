<?php

namespace OpenDialogAi\ConversationLog\tests;

use Illuminate\Support\Str;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\ConversationLog\ChatbotUser;
use OpenDialogAi\Core\Tests\TestCase;

class MessageTest extends TestCase
{
    public function testIntents()
    {
        $this->createMessage('message1', ['intent_1', "intent_2", "intent_23"]);
        $this->createMessage('message2', ['intent_2', "intent_3", "intent_12"]);

        $this->assertEquals(1, Message::containingIntent('intent_1')->count());
        $this->assertEquals(2, Message::containingIntent('intent_2')->count());
        $this->assertEquals(1, Message::containingIntent('intent_3')->count());
    }

    public function testMultipleIntents()
    {
        $this->createMessage('message1', ['intent_1', "intent_2", "intent_23"]);
        $this->createMessage('message2', ['intent_2', "intent_3", "intent_12"]);

        $this->assertEquals(2, Message::containingIntents(['intent_1', 'intent_2', 'intent_3'])->count());
        $this->assertEquals(2, Message::containingIntents(['intent_2'])->count());
        $this->assertEquals(1, Message::containingIntents(['intent_3'])->count());
        $this->assertEquals(0, Message::containingIntents([])->count());
    }

    private function createMessage($message, $intents): void
    {
        $userId = Str::random(20);

        (new ChatbotUser(['user_id' => $userId]))->save();

        (new Message([
            'user_id' => $userId,
            'author' => 'them',
            'message' => $message,
            'message_id' => Str::random(20),
            'type' => 'button',
            'microtime' => date('Y-m-d') . ' 10:35:06.340100',
            'intents' => $intents,
            'conversation' => 'welcome',
            'scene' => 'opening_scene',
        ]))->save();
    }
}
