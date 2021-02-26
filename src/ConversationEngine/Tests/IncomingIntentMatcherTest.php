<?php


namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Reasoners\IncomingIntentMatcher;
use OpenDialogAi\Core\Tests\TestCase;

class IncomingIntentMatcherTest extends TestCase
{
    public function testNoMatchingIntents()
    {
        // Mock selectors

        // Set conversational state

        $this->expectException(NoMatchingIntentsException::class);
        IncomingIntentMatcher::matchIncomingIntent();
    }

    public function testBasicAsRequestMatch()
    {
        // Mock selectors

        // Set conversational state

        $incomingIntent = IncomingIntentMatcher::matchIncomingIntent();

        // Assert is expected intent
    }
}
