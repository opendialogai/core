<?php

namespace OpenDialogAi\Core\Tests\Unit\Conversation;

use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Tests\TestCase;

class ParticipantTest extends TestCase
{
    const CONVERSATION = 'test_conversation';
    const OPENING_SCENE = 'opening_scene';
    const NEXT_SCENE = 'next_scene';

    const INTENT_USER_TO_BOT_1 = 'say_hello';
    const INTENT_BOT_TO_USER_2 = 'send_hello';
    const INTENT_USER_TO_BOT_3 = 'ask_question';
    const INTENT_USER_TO_BOT_4 = 'something_new';
    const INTENT_BOT_TO_USER_4 = 'send_answer';
    const INTENT_USER_TO_BOT_5 = 'say_goodbye';
    const INTENT_BOT_TO_USER_6 = 'send_goodbye';
    const INTENT_BOT_TO_USER_7 = 'say_goodbye2';

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

    public function setupConversationWithOneScene()
    {
        $cm = new ConversationManager(self::CONVERSATION, Conversation::ACTIVATED, 0);
        $cm->createScene(self::OPENING_SCENE, true);

        $this->intent1 = new Intent(self::INTENT_USER_TO_BOT_1);
        $this->intent1->setOrderAttribute(1);

        $this->intent2 = new Intent(self::INTENT_BOT_TO_USER_2);
        $this->intent2->setOrderAttribute(2);

        $this->intent3 = new Intent(self::INTENT_USER_TO_BOT_3);
        $this->intent3->setOrderAttribute(3);

        $this->intent4 = new Intent(self::INTENT_BOT_TO_USER_4);
        $this->intent4->setOrderAttribute(4);

        $this->intent5 = new Intent(self::INTENT_USER_TO_BOT_5);
        $this->intent5->setOrderAttribute(5);

        $this->intent6 = new Intent(self::INTENT_BOT_TO_USER_6, true);
        $this->intent6->setOrderAttribute(6);

        $cm->userSaysToBot(self::OPENING_SCENE, $this->intent1, 1)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent2, 2)
            ->userSaysToBot(self::OPENING_SCENE, $this->intent3, 3)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent4, 4)
            ->userSaysToBot(self::OPENING_SCENE, $this->intent5, 5)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent6, 6);

        return $cm;
    }

    public function setupConversationWithTwoScenes()
    {
        $cm = new ConversationManager(self::CONVERSATION, Conversation::ACTIVATED, 0);
        $cm->createScene(self::OPENING_SCENE, true);
        $cm->createScene(self::NEXT_SCENE, false);

        $this->intent1 = new Intent(self::INTENT_USER_TO_BOT_1);
        $this->intent1->setOrderAttribute(1);

        $this->intent2 = new Intent(self::INTENT_BOT_TO_USER_2);
        $this->intent2->setOrderAttribute(2);

        $this->intent3 = new Intent(self::INTENT_USER_TO_BOT_3);
        $this->intent3->setOrderAttribute(3);

        $this->intent4 = new Intent(self::INTENT_USER_TO_BOT_4);
        $this->intent4->setOrderAttribute(4);

        $this->intent5 = new Intent(self::INTENT_BOT_TO_USER_4, true);
        $this->intent5->setOrderAttribute(1);

        $this->intent6 = new Intent(self::INTENT_BOT_TO_USER_6);
        $this->intent6->setOrderAttribute(5);

        $this->intent7 = new Intent(self::INTENT_BOT_TO_USER_7, true);
        $this->intent7->setOrderAttribute(2);

        $cm->userSaysToBot(self::OPENING_SCENE, $this->intent1, 1)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent2, 2)
            ->userSaysToBot(self::OPENING_SCENE, $this->intent3, 3)
            ->userSaysToBotAcrossScenes(self::OPENING_SCENE, self::NEXT_SCENE, $this->intent4, 4)
            ->botSaysToUser(self::NEXT_SCENE, $this->intent5, 1)
            ->botSaysToUser(self::OPENING_SCENE, $this->intent6, 5)
            ->botSaysToUser(self::NEXT_SCENE, $this->intent7, 2);

        return $cm;
    }

    public function testGetAllIntentsSaidInOrderWithOneScene()
    {
        $cm = $this->setupConversationWithOneScene();
        $openingScene = $cm->getScene(self::OPENING_SCENE);

        $user = $openingScene->getUser();
        $userIntents = $user->getAllIntentsSaidInOrder();
        $this->assertEquals($this->intent1->getId(), $userIntents->skip(0)->value->getId());
        $this->assertEquals($this->intent3->getId(), $userIntents->skip(1)->value->getId());
        $this->assertEquals($this->intent5->getId(), $userIntents->skip(2)->value->getId());

        $bot = $openingScene->getBot();
        $botIntents = $bot->getAllIntentsSaidInOrder();
        $this->assertEquals($this->intent2->getId(), $botIntents->skip(0)->value->getId());
        $this->assertEquals($this->intent4->getId(), $botIntents->skip(1)->value->getId());
        $this->assertEquals($this->intent6->getId(), $botIntents->skip(2)->value->getId());
    }

    public function testGetAllIntentsSaidInOrderWithTwoScenes()
    {
        $cm = $this->setupConversationWithTwoScenes();
        $openingScene = $cm->getScene(self::OPENING_SCENE);
        $nextScene = $cm->getScene(self::NEXT_SCENE);

        $user = $openingScene->getUser();
        $userIntents = $user->getAllIntentsSaidInOrder();
        $this->assertEquals($this->intent1->getId(), $userIntents->skip(0)->value->getId());
        $this->assertEquals($this->intent3->getId(), $userIntents->skip(1)->value->getId());
        $this->assertEquals($this->intent4->getId(), $userIntents->skip(2)->value->getId());

        $bot = $openingScene->getBot();
        $botIntents = $bot->getAllIntentsSaidInOrder();
        $this->assertEquals($this->intent2->getId(), $botIntents->skip(0)->value->getId());
        $this->assertEquals($this->intent6->getId(), $botIntents->skip(1)->value->getId());

        $bot = $nextScene->getBot();
        $botIntents = $bot->getAllIntentsSaidInOrder();
        $this->assertEquals($this->intent5->getId(), $botIntents->skip(0)->value->getId());
        $this->assertEquals($this->intent7->getId(), $botIntents->skip(1)->value->getId());
    }
}
