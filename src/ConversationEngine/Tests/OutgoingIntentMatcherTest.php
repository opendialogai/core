<?php


namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\ConversationEngine\Exceptions\NoMatchingIntentsException;
use OpenDialogAi\ConversationEngine\Reasoners\OutgoingIntentMatcher;
use OpenDialogAi\Core\Tests\TestCase;

class OutgoingIntentMatcherTest extends TestCase
{
    public function testNoMatchingIntents()
    {
        // Mock selectors

        // Set conversational state

        $this->expectException(NoMatchingIntentsException::class);
        OutgoingIntentMatcher::matchOutgoingIntent();
    }

    public function testBasicAsResponseMatch()
    {
        // Mock selectors

        // Set conversational state

        $outgoingIntent = OutgoingIntentMatcher::matchOutgoingIntent();

        // Assert is expected intent
    }
}
