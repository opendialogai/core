<?php


namespace OpenDialogAi\Core\Conversation\Tests;


use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\ScenarioCollection;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;

class ConversationGenerator
{
    public const SCENARIO = 'test_scenario';
    public const CONVERSATION = 'test_conversation';
    public const SCENE = 'test_scene';
    public const TURN = 'test_turn';
    public const REQ_INTENT = 'intent.core.Req';
    public const RESP_INTENT = 'intent.core.Resp';


    public static function generateScenariosWithEverything($odId, int $count = 1): ScenarioCollection
    {
        $scenarios = new ScenarioCollection();

        for ($s = 1; $s <= $count; $s++) {
            $scenario = self::createScenario ($odId . '_' . self::SCENARIO . '_' . $s);
            for ($c=1; $c <= $count; $c++) {
                $conversation = self::createConversation($odId . '_' . self::CONVERSATION . '_' . $c);
                for ($sc = 1; $sc <= $count; $sc++) {
                    $scene = self::createScene($odId . '_' . self::SCENE . '_' . $sc);
                    for ($t = 1; $t <= $count; $t++) {
                        $turn = self::createTurn($odId . '_' . self::TURN . '_' . $t);
                        for ($i = 1; $i <= $count; $i++) {
                            $request_intent = self::createUserIntent($odId . '_' . self::REQ_INTENT . '_' . $t);
                            $turn->addRequestIntent($request_intent);
                            $response_intent = self::createAppIntent($odId . '_' . self::RESP_INTENT . '_' . $t);
                            $turn->addResponseIntent($response_intent);
                        }
                        $scene->addTurn($turn);
                    }
                    $conversation->addScene($scene);
                }
                $scenario->addConversation($conversation);
            }
            $scenarios->addObject($scenario);
        }
        return $scenarios;
    }

    public static function createScenario($odId)
    {
        $scenario = new Scenario();
        $scenario->setODId($odId);
        $scenario->setName($odId);
        $scenario->setDescription("This is a scenario called ". $odId);
        return $scenario;
    }

    public static function createConversation($odId): Conversation
    {
        $conversation = new Conversation();
        $conversation->setODId($odId);
        $conversation->setName($odId);
        $conversation->setDescription("This is a conversation for " . $odId);

        return $conversation;
    }

    public static function createScene($odId): Scene
    {
        $scene = new Scene();
        $scene->setODId($odId);
        $scene->setName($odId);
        $scene->setDescription("This is a scene for " . $odId);

        return $scene;
    }

    public static function createTurn($odId): Turn
    {
        $turn = new Turn();
        $turn->setODId($odId);
        $turn->setName($odId);
        $turn->setDescription("This is a turn for test purposes");

        return $turn;
    }

    public static function createUserIntent($odId): Intent
    {
        $intent = new Intent();
        $intent->setODId($odId);
        $intent->setSpeaker(Intent::USER);

        return $intent;
    }

    public static function createAppIntent($odId): Intent
    {
        $intent = new Intent();
        $intent->setODId($odId);
        $intent->setSpeaker(Intent::APP);

        return $intent;
    }
}
