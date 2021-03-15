<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

use DateTime;
use OpenDialogAi\Core\Conversation\ActionsCollection;
use OpenDialogAi\Core\Conversation\Behavior;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\VirtualIntent;
use OpenDialogAi\Core\Conversation\VirtualIntentCollection;
use OpenDialogAi\Core\Tests\TestCase;

class SerializationTestCase extends TestCase
{

    public function getStandaloneScenario()
    {
        $scenario = new Scenario();
        $scenario->setUid("0x0001");
        $scenario->setOdId("test_scenario");
        $scenario->setName("Test Scenario");
        $scenario->setDescription("This is a test scenario.");
        $scenario->setBehaviors(new BehaviorsCollection([new Behavior("STARTING")]));
        $scenario->setConditions(new ConditionCollection());
        $scenario->setInterpreter("interpreter.core.callback");
        $scenario->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $scenario->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $scenario->deactivate();
        $scenario->setStatus(Scenario::DRAFT_STATUS);
        return $scenario;
    }

    public function getStandaloneConversation()
    {
        $conversation = new Conversation();
        $conversation->setUid("0x0002");
        $conversation->setOdId("test_conversation");
        $conversation->setName("Test Conversation");
        $conversation->setDescription("This is a test conversaation.");
        $conversation->setInterpreter("interpreter.core.callback");
        $conversation->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $conversation->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $conversation->setConditions(new ConditionCollection());
        $conversation->setBehaviors(new BehaviorsCollection());
        return $conversation;
    }

    public function getStandaloneScene()
    {

        $scene = new Scene();
        $scene->setUid("0x0003");
        $scene->setOdId("test_scene");
        $scene->setName("Test Scene");
        $scene->setDescription("This is a test scene.");
        $scene->setInterpreter("interpreter.core.callback");
        $scene->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $scene->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $scene->setConditions(new ConditionCollection());
        $scene->setBehaviors(new BehaviorsCollection());
        return $scene;
    }

    public function getStandaloneTurn()
    {
        $turn = new Turn();
        $turn->setUid("0x0004");
        $turn->setOdId("test_turn");
        $turn->setName("Test Turn");
        $turn->setDescription("This is a test turn.");
        $turn->setInterpreter("interpreter.core.callback");
        $turn->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $turn->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $turn->setValidOrigins(['other_test_turn', 'another_test_turn']);
        $turn->setConditions(new ConditionCollection());
        $turn->setBehaviors(new BehaviorsCollection());
        return $turn;
    }

    public function getStandaloneIntent()
    {
        $intent = new Intent();
        $intent->setUid("0x0005");
        $intent->setOdId("test_intent");
        $intent->setName("Test Intent");
        $intent->setDescription("This is a test intent.");
        $intent->setInterpreter("interpreter.core.callback");
        $intent->setCreatedAt(new DateTime('2021-03-01T01:00:00.0000Z'));
        $intent->setUpdatedAt(new DateTime('2021-03-01T02:00:00.0000Z'));
        $intent->setSpeaker(Intent::USER);
        $intent->setConfidence(1.0);
        $intent->setSampleUtterance("Test intent - sample utterance.");
        $intent->setTransition(new Transition
        (null, 'test_scene', 'test_turn'));
        $intent->setListensFor(['intent.core.TestA', 'intent.core.TestB']);
        $intent->setVirtualIntents(new VirtualIntentCollection([
            new
            VirtualIntent
            (Intent::USER, 'intent.core.Hello'), new VirtualIntent(Intent::APP, 'intent.core.Goodbye')
        ]));
        $intent->setExpectedAttributes(['user.name', 'session.startTime']);
        $intent->setActions(new ActionsCollection());
        $intent->setBehaviors(new BehaviorsCollection());
        $intent->setConditions(new ConditionCollection());

        return $intent;
    }

}
