<?php

namespace OpenDialogAi\ConversationEngine\Tests;


use OpenDialogAi\AttributeEngine\AttributeBag\BasicAttributeBag;
use OpenDialogAi\AttributeEngine\Contracts\AttributeBag;
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
        $intent = $this->createIntentWithInterpretation('test_intent', 1);
        $intents = new IntentCollection([$intent]);

        $topRankingIntent = IntentRanker::getTopRankingIntent($intents);

        $this->assertEquals($intent, $topRankingIntent);
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testWithMultipleIntentsNoAttributes()
    {
        $expectedIntent = $this->createIntentWithInterpretation('test_intent2', 0.75);

        $intentArray = [];
        $intentArray[] = $this->createIntentWithInterpretation('test_intent', 0);
        $intentArray[] = $expectedIntent;
        $intentArray[] = $this->createIntentWithInterpretation('test_intent3', 0.75);
        $intentArray[] = $this->createIntentWithInterpretation('test_intent4', 0.25);
        $intentArray[] = $this->createIntentWithInterpretation('test_intent5', 0.5);
        $intents = new IntentCollection($intentArray);

        $topRankingIntent = IntentRanker::getTopRankingIntent($intents);

        // Two intents shared the highest confidence (0.75), we should take the one that appears earliest in the collection
        $this->assertSame($expectedIntent, $topRankingIntent);
    }

    /**
     * @throws EmptyCollectionException
     */
    public function testWithMultipleIntentsWithAttributes()
    {
        $expectedAttributeBag = new BasicAttributeBag();
        $expectedAttributeBag->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $expectedAttributeBag->addAttribute(AttributeResolver::getAttributeFor('last_name', 'test'));
        $expectedIntent = $this->createIntentWithInterpretationWithAttributes(
            'test_intent3',
            0.75,
            $expectedAttributeBag
        );

        $notExpectedAttributeBag = new BasicAttributeBag();
        $notExpectedAttributeBag->addAttribute(AttributeResolver::getAttributeFor('first_name', 'test'));
        $notExpectedIntent = $this->createIntentWithInterpretationWithAttributes(
            'test_intent2',
            0.75,
            $notExpectedAttributeBag
        );

        $intentArray = [];
        $intentArray[] = $this->createIntentWithInterpretation('test_intent', 0);
        $intentArray[] = $notExpectedIntent;
        $intentArray[] = $expectedIntent;
        $intentArray[] = $this->createIntentWithInterpretation('test_intent4', 0.25);
        $intentArray[] = $this->createIntentWithInterpretation('test_intent5', 0.5);
        $intents = new IntentCollection($intentArray);

        $topRankingIntent = IntentRanker::getTopRankingIntent($intents);

        // Two intents shared the highest confidence (0.75), but one had more (matching expected) attributes
        $this->assertSame($expectedIntent, $topRankingIntent);
    }

    /**
     * @param string $id
     * @param float $confidence
     * @return Intent
     */
    private function createIntentWithInterpretation(string $id, float $confidence): Intent
    {
        $intent = Intent::createIntent($id, $confidence);

        $intent->addInterpretedIntents(new IntentCollection([clone $intent]));
        $intent->checkForMatch();

        return $intent;
    }

    /**
     * @param string $id
     * @param float $confidence
     * @param AttributeBag $attributeBag
     * @return Intent
     */
    private function createIntentWithInterpretationWithAttributes(string $id, float $confidence, AttributeBag $attributeBag): Intent
    {
        $intent = $this->createIntentWithInterpretation($id, $confidence);
        $interpretedIntent = $intent->getInterpretation();

        foreach ($attributeBag->getAttributes() as $attribute) {
            $interpretedIntent->addAttribute($attribute);
        }

        return $intent;
    }
}
