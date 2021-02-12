<?php
namespace OpenDialogAi\Conversation\Tests;


use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\CannotAddObjectWithoutODidException;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationModelStructureTests extends TestCase
{
    public const INTERPRETER = 'interpreter.core.nlp';

    public function testScenarioStructure()
    {
        $scenario = new Scenario();
        $this->assertNotNull($scenario->getConversations());
        $this->assertEquals(0, count($scenario->getConversations()));

        $conversation = new Conversation();
        $this->expectException(CannotAddObjectWithoutODidException::class);
        $scenario->addConversation($conversation);

        $conversation->setODId('conversation');
        $scenario->addConversation($conversation);

        $this->assertEquals(1, count($scenario->getConversations()));

        $this->assertNull($scenario->getInterpreter());
        $scenario->setInterpreter(self::INTERPRETER);
        $this->assertEquals(self::INTERPRETER, $scenario->getInterpreter());
    }

    public function testInterpreterChaining()
    {
        $scenario = new Scenario();
        $scenario->setODId('scenario');
        $scenario->setInterpreter(self::INTERPRETER);

        $conversation = new Conversation($scenario);
        $scene = new Scene($conversation);
        $turn = new Turn($scene);
        $intent = new Intent($turn);

        $this->assertEquals(self::INTERPRETER, $intent->getInterpreter());

        $conversation->setInterpreter(self::INTERPRETER.'1');
        $this->assertEquals(self::INTERPRETER.'1', $intent->getInterpreter());

        $scene->setInterpreter(self::INTERPRETER.'2');
        $this->assertEquals(self::INTERPRETER.'2', $intent->getInterpreter());

        $turn->setInterpreter(self::INTERPRETER.'3');
        $this->assertEquals(self::INTERPRETER.'3', $intent->getInterpreter());

        $intent->setInterpreter(self::INTERPRETER.'4');
        $this->assertEquals(self::INTERPRETER.'4', $intent->getInterpreter());

    }

}
