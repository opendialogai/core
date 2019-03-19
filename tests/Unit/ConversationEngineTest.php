<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\ConversationEngine\Conversation;
use OpenDialogAi\ConversationEngine\ConversationLog;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationEngineTest extends TestCase
{
    public function testConversationDb()
    {
        // Ensure that we can create a conversation.
        Conversation::create(['name' => 'Test Conversation', 'model' => 'conversation:']);
        $conversation = Conversation::where('name', 'Test Conversation')->first();
        $this->assertEquals('Test Conversation', $conversation->name);
    }

    public function testConversationDbRelationships()
    {
        Conversation::create(['name' => 'Test Conversation', 'model' => 'conversation:']);
        $conversation = Conversation::where('name', 'Test Conversation')->first();

        ConversationLog::create([
            'conversation_id' => $conversation->id,
            'message' => 'new revision',
            'type' => 'update',
        ]);
        $conversationLog = ConversationLog::where('message', 'new revision')->first();

        // Ensure we can get a ConversationLog's Conversation.
        $this->assertEquals($conversation->id, $conversationLog->conversation->id);

        // Ensure we can get a Conversation's ConversationLogs.
        $this->assertTrue($conversation->conversationLogs->contains($conversationLog));
    }
}
