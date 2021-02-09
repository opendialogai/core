<?php


namespace OpenDialogAi\Core\Tests\Utils;


use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class ConversationGenerator
{
    public static function generateSimpleScenario(): Scenario
    {
        $scenario = new Scenario();
        $scenario->setODId('test_scenario');
        $scenario->setName('Test Scenario');
        $scenario->setDescription("This is a scenario for test purposes");

        return $scenario;
    }

    public static function generateSimpleConversation(): Conversation
    {
        $conversation = new Conversation();
        $conversation->setODId('test_conversation');
        $conversation->setName('Test Conversation');
        $conversation->setDescription("This is a conversation for test purposes");

        return $conversation;
    }

    public static function generateSimpleScene(): Scene
    {
        $scene = new Scene();
        $scene->setODId('test_scene');
        $scene->setName('Test Scene');
        $scene->setDescription("This is a scene for test purposes");

        return $scene;
    }

    public static function generateSimpleTurn(): Turn
    {
        $turn = new Turn();
        $turn->setODId('test_turn');
        $turn->setName('Test Turn');
        $turn->setDescription("This is a turn for test purposes");

        return $turn;
    }

    public static function generateSimpleUserIntent(): Intent
    {
        $intent = new Intent();
        $intent->setODId('intent.core.welcome');
        $intent->setSpeaker(Intent::USER);

        return $intent;
    }

    public static function generateSimpleAppIntent(): Intent
    {
        $intent = new Intent();
        $intent->setODId('intent.core.welcomeReply');
        $intent->setSpeaker(Intent::APP);

        return $intent;
    }

    public static function generateSimpleScenarioWithConversation(): Scenario
    {
        $scenario = ConversationGenerator::generateSimpleScenario();
        $conversation = ConversationGenerator::generateSimpleConversation();
        $scene = ConversationGenerator::generateSimpleScene();
        $turn = ConversationGenerator::generateSimpleTurn();
        $requestIntent = ConversationGenerator::generateSimpleUserIntent();
        $responseIntent = ConversationGenerator::generateSimpleAppIntent();

        $turn->addRequestIntent($requestIntent);
        $turn->addResponseIntent($responseIntent);
        $scene->addTurn($turn);
        $conversation->addScene($scene);
        $scenario->addConversation($conversation);

        return $scenario;
    }
}
