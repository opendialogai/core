<?php

namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\AttributeEngine\Facades\AttributeResolver;
use OpenDialogAi\ConversationEngine\Exceptions\EmptyCollectionException;
use OpenDialogAi\ConversationEngine\Reasoners\IntentRanker;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Tests\TestCase;

class IntentRankerTest extends TestCase
{
    public function testWithEmptyIntentCollection()
    {
        $intents = new IntentCollection();

        $this->expectException(EmptyCollectionException::class);

        IntentRanker::getTopRankingIntent($intents);
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testWithSingleIntent()
    {
        $intent = Intent::createIntent('test_intent', 1);
        $intents = new IntentCollection([$intent]);

        $topRankingIntent = IntentRanker::getTopRankingIntent($intents);

        $this->assertEquals($intent, $topRankingIntent);
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testWithMultipleIntentsNoAttributes()
    {
        $expectedIntent = Intent::createIntent('test_intent2', 0.75);

        $intentArray = [];
        $intentArray[] = Intent::createIntent('test_intent', 0);
        $intentArray[] = $expectedIntent;
        $intentArray[] = Intent::createIntent('test_intent3', 0.75);
        $intentArray[] = Intent::createIntent('test_intent4', 0.25);
        $intentArray[] = Intent::createIntent('test_intent5', 0.5);
        $intents = new IntentCollection([$intentArray]);

        $topRankingIntent = IntentRanker::getTopRankingIntent($intents);

        // Two intents shared the highest confidence (0.75), we should take the one that appears earliest in the collection
        $this->assertEquals($expectedIntent, $topRankingIntent);
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testWithMultipleIntentsWithAttributes()
    {
        $expectedIntent = Intent::createIntent('test_intent3', 0.75);
        $expectedIntent->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $expectedIntent->addAttribute(AttributeResolver::getAttributeFor('last_name', 'test'));

        $notExpectedIntent = Intent::createIntent('test_intent2', 0.75);
        $notExpectedIntent->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));

        $intentArray = [];
        $intentArray[] = Intent::createIntent('test_intent', 0);
        $intentArray[] = $notExpectedIntent;
        $intentArray[] = $expectedIntent;
        $intentArray[] = Intent::createIntent('test_intent4', 0.25);
        $intentArray[] = Intent::createIntent('test_intent5', 0.5);
        $intents = new IntentCollection([$intentArray]);

        $topRankingIntent = IntentRanker::getTopRankingIntent($intents);

        // Two intents shared the highest confidence (0.75), but one had more (matching expected) attributes
        $this->assertEquals($expectedIntent, $topRankingIntent);
    }
}
