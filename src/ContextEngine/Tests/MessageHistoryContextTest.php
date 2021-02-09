<?php

namespace OpenDialogAi\ContextEngine\Tests;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class MessageHistoryContextTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group skip
     * @requires DGRAPH
     */
    public function testMessageHistoryContext()
    {
        $utterance = UtteranceGenerator::generateTextUtterance('test');
        ContextService::createUserContext($utterance);

        $userId = ContextService::getUserContext()->getUserId();

        $message = Message::create(microtime(), 'text', $userId, 'me', 'send message');
        $message->save();

        $message = Message::create(microtime(), 'text', $userId, 'them', 'received message');
        $message->save();

        $messageHistoryAttribute = ContextService::getAttributeValue('all', 'message_history');

        $this->assertStringContainsString(urlencode('User: send message'), $messageHistoryAttribute);
        $this->assertStringContainsString(urlencode('Bot: received message'), $messageHistoryAttribute);
    }
}
