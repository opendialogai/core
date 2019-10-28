<?php

namespace OpenDialogAi\Core\Tests\Unit\Conversation;

use Ds\Map;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelIntent;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Tests\TestCase;

class ScenesTest extends TestCase
{
    const CONVERSATION = 'test_conversation';
    const OPENING_SCENE = 'test_opening_scene';
    const LATEST_NEWS_SCENE = 'test_latest_news_scene';
    const CONTINUE_WITH_AUDIT_SCENE = 'test_continue_with_audit_scene';

    const INTENT_USER_TO_BOT_1 = 'intent.core.hi';
    const INTENT_BOT_TO_USER_2 = 'intent.core.welcome_and_choose';
    const INTENT_USER_TO_BOT_3 = 'intent.core.continue_with_audit';
    const INTENT_USER_TO_BOT_4 = 'intent.core.get_the_latest_news';
    const INTENT_BOT_TO_USER_5 = 'intent.core.here_are_the_news';
    const INTENT_USER_TO_BOT_6 = 'intent.core.accept_news';
    const INTENT_BOT_TO_USER_7 = 'intent.core.complete_news';
    const INTENT_BOT_TO_USER_8 = 'intent.core.complete_news2';
    const INTENT_BOT_TO_USER_9 = 'intent.core.here_is_the_audit';
    const INTENT_BOT_TO_USER_10 = 'intent.core.here_is_the_audit2';
    const INTENT_USER_TO_BOT_11 = 'intent.core.accept_audit';
    const INTENT_BOT_TO_USER_12 = 'intent.core.complete_audit';
    /**
     * @var Intent
     */
    private $intent1;

    /**
     * @var Intent
     */
    private $intent2;

    /**
     * @var Intent
     */
    private $intent3;

    /**
     * @var Intent
     */
    private $intent4;

    /**
     * @var Intent
     */
    private $intent5;

    /**
     * @var Intent
     */
    private $intent6;

    /**
     * @var Intent
     */
    private $intent7;

    /**
     * @var Intent
     */
    private $intent8;

    /**
     * @var Intent
     */
    private $intent9;

    /**
     * @var Intent
     */
    private $intent10;

    /**
     * @var Intent
     */
    private $intent11;

    /**
     * @var Intent
     */
    private $intent12;

    public function setupConversation()
    {
        // Create a conversation manager and setup a conversation
        $cm = new ConversationManager(self::CONVERSATION, Conversation::ACTIVATED, 0);

        $cm->createScene(self::OPENING_SCENE, true);
        $cm->createScene(self::LATEST_NEWS_SCENE, false);

        // Add an intent from one participant to the other
        $this->intent1 = new Intent(self::INTENT_USER_TO_BOT_1);
        $this->intent1->setOrderAttribute(1);

        $this->intent2 = new Intent(self::INTENT_BOT_TO_USER_2);
        $this->intent2->setOrderAttribute(2);

        $this->intent3 = new Intent(self::INTENT_USER_TO_BOT_3);
        $this->intent3->setOrderAttribute(3);

        $this->intent4 = new Intent(self::INTENT_USER_TO_BOT_4);
        $this->intent4->setOrderAttribute(4);

        $this->intent5 = new Intent(self::INTENT_BOT_TO_USER_5);
        $this->intent5->setOrderAttribute(1);

        $this->intent6 = new Intent(self::INTENT_BOT_TO_USER_7);
        $this->intent6->setOrderAttribute(2);

        $this->intent7 = new Intent(self::INTENT_USER_TO_BOT_6);
        $this->intent7->setOrderAttribute(3);

        $this->intent8 = new Intent(self::INTENT_BOT_TO_USER_8, true);
        $this->intent8->setOrderAttribute(4);

        $this->intent9 = new Intent(self::INTENT_BOT_TO_USER_9);
        $this->intent9->setOrderAttribute(6);

        $this->intent10 = new Intent(self::INTENT_BOT_TO_USER_10);
        $this->intent10->setOrderAttribute(7);

        $this->intent11 = new Intent(self::INTENT_USER_TO_BOT_11);
        $this->intent11->setOrderAttribute(8);

        $this->intent12 = new Intent(self::INTENT_BOT_TO_USER_12, true);
        $this->intent12->setOrderAttribute(9);

        $cm->userSaysToBot(self::OPENING_SCENE, $this->intent1, 1)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent2, 2)
            ->userSaysToBot(self::OPENING_SCENE, $this->intent3, 3)
            ->userSaysToBotAcrossScenes(self::OPENING_SCENE, self::LATEST_NEWS_SCENE, $this->intent4, 4)
            ->botSaysToUser(self::LATEST_NEWS_SCENE, $this->intent5, 1)
            ->botSaysToUser(self::LATEST_NEWS_SCENE, $this->intent6, 2)
            ->userSaysToBot(self::LATEST_NEWS_SCENE, $this->intent7, 3)
            ->botSaysToUser(self::LATEST_NEWS_SCENE, $this->intent8, 4)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent9, 6)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent10, 7)
            ->userSaysToBot(self::OPENING_SCENE, $this->intent11, 8)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent12, 9);

        return $cm;
    }

    public function testAddOpeningScene()
    {
        $cm = $this->setupConversation();
        $conversation = $cm->getConversation();

        $this->assertTrue($conversation->getId() == self::CONVERSATION);

        $this->assertTrue($conversation->hasOpeningScene());

        // Go from the conversation to the scene
        /* @var Map $toOpeningScenes */
        $openingScenes = $conversation->getOpeningScenes();

        $this->assertTrue(count($openingScenes) == 1);
        $this->assertTrue($openingScenes->first()->toArray()['value']->getId() == self::OPENING_SCENE);
    }

    public function testGetNextPossibleBotIntents()
    {
        $cm = $this->setupConversation();
        $openingScene = $cm->getScene(self::OPENING_SCENE);
        $latestNewsScene = $cm->getScene(self::LATEST_NEWS_SCENE);

        /** @var EIModelCreator $eiModelCreator */
        $eiModelCreator = app()->make(EIModelCreator::class);

        // Test that to begin with it returns just intent2
        $possibleIntents = $openingScene->getNextPossibleBotIntents($this->intent1);
        $this->assertEquals(1, $possibleIntents->count());
        $this->assertEquals($this->intent2->getId(), $possibleIntents->first()->value->getId());

        // Test that it returns the correct intents when the current intent is intent3 and that they are in the right order
        $possibleIntents = $openingScene->getNextPossibleBotIntents($this->intent3);
        $this->assertEquals(2, $possibleIntents->count());
        $this->assertEquals($this->intent9->getId(), $possibleIntents->first()->value->getId());
        $this->assertEquals($this->intent10->getId(), $possibleIntents->get(self::INTENT_BOT_TO_USER_10)->getId());

        // Test that it returns the correct intents when the current intent is said across scenes and that they are in the right order
        $possibleIntents = $latestNewsScene->getNextPossibleBotIntents($this->intent4);
        $this->assertEquals(2, $possibleIntents->count());
        $this->assertEquals($this->intent5->getId(), $possibleIntents->first()->value->getId());
        $this->assertEquals($this->intent6->getId(), $possibleIntents->get(self::INTENT_BOT_TO_USER_7)->getId());
    }
}
