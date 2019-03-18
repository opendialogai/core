<?php


namespace OpenDialogAi\Core\Tests\Unit\Conversation;


use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Tests\TestCase;

class ScenesTest extends TestCase
{
    const CONVERSATION = 'test_conversation';
    const OPENING_SCENE = 'test_opening_scene';
    const LATEST_NEWS_SCENE = 'test_latest_news_scene';
    const CONTINUE_WITH_AUDIT_SCENE = 'test_continue_with_audit_scene';

    public function setupConversation()
    {
        // Create a conversation manager and setup a conversation
        $cm = new ConversationManager(self::CONVERSATION);

        $scene1 = $cm->createScene(self::OPENING_SCENE, true);
        $scene2 = $cm->createScene(self::LATEST_NEWS_SCENE, false);
        $scene3 = $cm->createScene(self::CONTINUE_WITH_AUDIT_SCENE, false);

        return $cm;
    }

    /**
     *
     */
    public function testAddOpeningScene()
    {
        $cm = $this->setupConversation();
        /* @var Conversation $conversation */
        $conversation = $cm->getConversation();

        $this->assertTrue($conversation->getId() == self::CONVERSATION);

        $this->assertTrue($conversation->hasOpeningScene());

        // Go from the conversation to the scene
        /* @var Map $toOpeningScenes */
        $openingScenes = $conversation->getOpeningScenes();

        $this->assertTrue(count($openingScenes) == 1);
        $this->assertTrue($openingScenes->first()->toArray()['value']->getId() == self::OPENING_SCENE);
    }

}