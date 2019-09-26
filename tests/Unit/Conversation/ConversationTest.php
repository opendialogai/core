<?php

namespace OpenDialogAi\Core\Tests\Unit\Conversation;

use Ds\Map;
use OpenDialogAi\Core\Attribute\BooleanAttribute;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Conversation\Action;
use OpenDialogAi\Core\Conversation\Condition;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\ConversationManager;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\InvalidConversationStatusTransitionException;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Attribute\AbstractAttribute;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationTest extends TestCase
{
    const CONVERSATION = 'test_conversation';
    const CONDITION1 = 'chatbot_user_registered';
    const CONDITION2 = 'last_chatbot_message_posted';
    const REGISTERED_USER_STATUS = 'registered_user_status';
    const TIME_SINCE_LAST_COMMENT = 'time_since_last_comment';
    const OPENING_SCENE = 'test_opening_scene';
    const LATEST_NEWS_SCENE = 'test_latest_news_scene';
    const CONTINUE_WITH_AUDIT_SCENE = 'test_continue_with_audit_scene';

    const INTENT_USER_TO_BOT_1 = 'intent.core.hi';
    const INTENT_BOT_TO_USER_2 = 'intent.core.welcome_and_choose';
    const INTENT_USER_TO_BOT_3 = 'intent.core.get_the_latest_news';
    const INTENT_BOT_TO_USER_4 = 'intent.core.here_are_the_news';
    const INTENT_USER_TO_BOT_5 = 'intent.core.continue_with_audit';
    const INTENT_BOT_TO_USER_6 = 'intent.core.here_is_the_audit';

    public function setupConversation()
    {
        // Create a conversation manager and setup a conversation
        $cm = new ConversationManager(self::CONVERSATION, Conversation::SAVED, 0);

        $condition1 = new Condition(
            new BooleanAttribute(self::REGISTERED_USER_STATUS, true),
            AbstractAttribute::EQUIVALENCE,
            self::CONDITION1
        );

        $condition2 = new Condition(
            new IntAttribute(self::TIME_SINCE_LAST_COMMENT, 10000),
            AbstractAttribute::GREATER_THAN_OR_EQUAL,
            self::CONDITION2
        );

        $cm->createScene(self::OPENING_SCENE, true)
            ->createScene(self::LATEST_NEWS_SCENE, false)
            ->createScene(self::CONTINUE_WITH_AUDIT_SCENE, false);

        // Add conditions to scene
        $cm->addConditionToScene(self::OPENING_SCENE, $condition1)
            ->addConditionToScene(self::OPENING_SCENE, $condition2);

        // Add an intent from one participant to the other
        $intent1 = new Intent(self::INTENT_USER_TO_BOT_1);
        $intent2 = new Intent(self::INTENT_BOT_TO_USER_2);
        $intent3 = new Intent(self::INTENT_USER_TO_BOT_3);
        $intent4 = new Intent(self::INTENT_BOT_TO_USER_4, true);
        $intent4->addAction(new Action('action.core.getNews'));
        $intent5 = new Intent(self::INTENT_USER_TO_BOT_5);
        $intent6 = new Intent(self::INTENT_BOT_TO_USER_6, true);

        $cm->userSaysToBot(self::OPENING_SCENE, $intent1, 1)
            ->botSaysToUser(self::OPENING_SCENE, $intent2, 2)
            ->userSaysToBotAcrossScenes(self::OPENING_SCENE, self::LATEST_NEWS_SCENE, $intent3, 3)
            ->botSaysToUser(self::LATEST_NEWS_SCENE, $intent4, 4)
            ->userSaysToBotAcrossScenes(self::OPENING_SCENE, self::CONTINUE_WITH_AUDIT_SCENE, $intent5, 5)
            ->botSaysToUser(self::CONTINUE_WITH_AUDIT_SCENE, $intent6, 6);

        try {
            $cm->setValidated();
        } catch (InvalidConversationStatusTransitionException $e) {
            $this->fail($e->getMessage());
        }

        return $cm;
    }


    /**
     *
     */
    public function testParticipantsExistInScene()
    {
        $cm = $this->setupConversation();
        /* @var Conversation @conversation */
        $conversation = $cm->getConversation();

        /* @var Map $openingScenes */
        $openingScenes = $conversation->getOpeningScenes();

        /* @var Scene $openingScene */
        $openingScene = $openingScenes->first()->toArray()['value'];

        $this->assertTrue($openingScene->getBot()->getId() == $openingScene->botIdInScene());
        $this->assertTrue($openingScene->getUser()->getId() == $openingScene->userIdInScene());

        // Traverse the graph to get to the participants
        /* @var Map $nodes */
        $nodes = $openingScene->getOutgoingEdgesWithRelationship(Model::HAS_BOT_PARTICIPANT)->getToNodes();

        $this->assertTrue(count($nodes) == 1);

        $this->assertTrue($nodes->first()->toArray()['value']->getId() == $openingScene->botIdInScene());
    }

    public function testConditionsAreAssosiatedWithScene()
    {
        $cm = $this->setupConversation();
        $conversation = $cm->getConversation();

        /* @var Scene scene */
        $scene = $conversation->getScene(self::OPENING_SCENE);

        /* @var Map $conditions */
        $conditions = $scene->getConditions();

        $this->assertTrue(count($conditions) == 2);

        $this->assertTrue($scene->getCondition(self::CONDITION1)->getId() == self::CONDITION1);
        $this->assertTrue($scene->getCondition(self::CONDITION2)->getId() == self::CONDITION2);
        $this->assertTrue($scene->getCondition(self::CONDITION1)->getId() != self::CONDITION2);
    }

    public function testConversationState() {
        $cm = $this->setupConversation();
        $conversation = $cm->getConversation();

        $this->assertEquals(0, $conversation->getAttribute(Model::CONVERSATION_VERSION)->getValue());
        $this->assertEquals(Conversation::ACTIVATABLE, $conversation->getAttribute(Model::CONVERSATION_STATUS)->getValue());
        $this->assertFalse($conversation->hasUpdateOf());

        try {
            $cm->setActivated();
        } catch (InvalidConversationStatusTransitionException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals(Conversation::ACTIVATED, $conversation->getAttribute(Model::CONVERSATION_STATUS)->getValue());

        $conversation_updated = clone $conversation;
        $conversation_updated->getScene(self::LATEST_NEWS_SCENE)->setId(self::LATEST_NEWS_SCENE . "2");
        $conversation_updated->setConversationVersion(1);
        $conversation_updated->setConversationStatus(Conversation::ACTIVATABLE);
        $conversation_updated->setUpdateOf($conversation);

        $this->assertEquals(1, $conversation_updated->getAttribute(Model::CONVERSATION_VERSION)->getValue());
        $this->assertEquals(Conversation::ACTIVATABLE, $conversation_updated->getAttribute(Model::CONVERSATION_STATUS)->getValue());
        $this->assertTrue($conversation->hasUpdateOf());

        /** @var Conversation $updateOf */
        $updateOf = $conversation_updated->getUpdateOf();

        $this->assertEquals($conversation->getUid(), $updateOf->getUid());
        $this->assertEquals($conversation->getId(), $updateOf->getId());
    }
}
