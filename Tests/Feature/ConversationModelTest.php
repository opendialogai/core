<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\ConversationGenerator;

class ConversationModelTest extends TestCase
{
    public function testScenarioCreation()
    {
        $scenario = ConversationGenerator::generateSimpleScenario();

        $this->assertEquals('test_scenario', $scenario->getODId());
        $this->assertEquals('Test Scenario', $scenario->getName());
        $this->assertEquals('This is a scenario for test purposes', $scenario->getDescription());
    }

    public function testConversationCreation()
    {
        $conversation = ConversationGenerator::generateSimpleConversation();

        $this->assertEquals('test_conversation', $conversation->getODId());
        $this->assertEquals('Test Conversation', $conversation->getName());
        $this->assertEquals('This is a conversation for test purposes', $conversation->getDescription());
    }

    public function testSceneCreation()
    {
        $scene = ConversationGenerator::generateSimpleScene();

        $this->assertEquals('test_scene', $scene->getODId());
        $this->assertEquals('Test Scene', $scene->getName());
        $this->assertEquals('This is a scene for test purposes', $scene->getDescription());
    }

    public function testTurnCreation()
    {
        $turn = ConversationGenerator::generateSimpleTurn();

        $this->assertEquals('test_turn', $turn->getODId());
        $this->assertEquals('Test Turn', $turn->getName());
        $this->assertEquals('This is a turn for test purposes', $turn->getDescription());
    }

    public function testIntentCreation()
    {
        $intent = ConversationGenerator::generateSimpleUserIntent();

        $this->assertEquals('intent.core.welcome', $intent->getODId());
    }

    public function testScenarioWithConversation()
    {
        $scenario = ConversationGenerator::generateSimpleScenarioWithConversation();
        $conversation = $scenario->getConversation('test_conversation');

        $this->assertTrue($scenario->hasConversations());
        $this->assertEquals(1, count($scenario->getConversations()));
        $this->assertEquals('test_conversation', $conversation->getODId());

        $scene = $conversation->getScene('test_scene');
        $this->assertTrue($conversation->hasScenes());
        $this->assertEquals(1, count($conversation->getScenes()));
        $this->assertEquals('test_scene', $scene->getODId());

        $turn = $scene->getTurn('test_turn');
        $this->assertTrue($scene->hasTurns());
        $this->assertEquals(1, count($scene->getTurns()));
        $this->assertEquals('test_turn', $turn->getODId());

        $requestIntents = $turn->getRequestIntents();
        $this->assertTrue($turn->hasRequestIntents());
        $this->assertEquals(1, count($requestIntents));
        $responseIntents = $turn->getResponseIntents();
        $this->assertTrue($turn->hasResponseIntents());
        $this->assertEquals(1, count($responseIntents));


        $requestIntent = $turn->getRequestIntent('intent.core.welcome');
        $this->assertEquals('intent.core.welcome', $requestIntent->getODId());
        $responseIntent = $turn->getResponseIntent('intent.core.welcomeReply');
        $this->assertEquals('intent.core.welcomeReply', $responseIntent->getODId());

    }

}
