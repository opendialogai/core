<?php


namespace OpenDialogAi\Core\Conversation\DataClients\Tests;

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
use \DateTime;

class SerializationTestCase extends TestCase
{

    public function getStandaloneScenario() {
        return new Scenario("0x0001", "test_scenario", "Test Scenario", "This is a test scenario.", new
        ConditionCollection(), new BehaviorsCollection([new Behavior("STARTING")]), "interpreter.core.callback", new DateTime('2021-03-01T01:00:00.0000Z'),
            new DateTime('2021-03-01T02:00:00.0000Z'), false, Scenario::DRAFT_STATUS);
    }

    public function getStandaloneConversation() {
        return new Conversation("0x0002", "test_conversation", "Test Conversation", "This is a test conversation.", new
        ConditionCollection(), new BehaviorsCollection(), "interpreter.core.callback", new DateTime('2021-03-01T01:00:00.0000Z'),
            new DateTime('2021-03-01T02:00:00.0000Z'));
    }

    public function getStandaloneScene() {
        return new Scene("0x0003", "test_scene", "Test Conversation", "This is a test scene.", new
        ConditionCollection(), new BehaviorsCollection(), "interpreter.core.callback", new DateTime('2021-03-01T01:00:00.0000Z'),
            new DateTime('2021-03-01T02:00:00.0000Z'));
    }

    public function getStandaloneTurn() {
        return new Turn("0x0004", "test_turn", "Test Turn", "This is a test turn.", new
        ConditionCollection(), new BehaviorsCollection(), "interpreter.core.callback", new DateTime('2021-03-01T01:00:00.0000Z'),
            new DateTime('2021-03-01T02:00:00.0000Z'), ['other_test_turn', 'another_test_turn']);
    }

    public function getStandaloneIntent() {
        return new Intent("0x0005", "test_intent", "Test Intent", "This is a test intent.", new
        ConditionCollection(), new BehaviorsCollection(), "interpreter.core.callback", new DateTime('2021-03-01T01:00:00.0000Z'),
            new DateTime('2021-03-01T02:00:00.0000Z'), Intent::USER, 1.0, "Test intent - sample utterance.", new Transition
            (null, 'test_scene', 'test_turn'), ['interpreter.core.TestA', 'interpreter.core.TestB'], new
            VirtualIntentCollection([new
        VirtualIntent
            (Intent::USER, 'intent.core.Hello'), new VirtualIntent(Intent::APP, 'intent.core.Goodbye')]), ['user.name', 'session.startTime'], new ActionsCollection());
    }

}
