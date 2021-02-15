<?php
namespace OpenDialogAi\Conversation\Tests;


use OpenDialogAi\Core\Conversation\Conversation;
use OpenDialogAi\Core\Conversation\Exceptions\CannotAddObjectWithoutODidException;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\IntentCollection;
use OpenDialogAi\Core\Conversation\Scenario;
use OpenDialogAi\Core\Conversation\Scene;
use OpenDialogAi\Core\Conversation\Tests\ConversationGenerator;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationModelStructureTest extends TestCase
{
    public const INTERPRETER = 'interpreter.core.nlp';

    public function testScenarioStructure()
    {
        $test = 'test';
        $count = 2;
        $scenarios = ConversationGenerator::generateScenariosWithEverything($test, $count);

        $this->assertCount($count, $scenarios);

        $first_scenario = $scenarios->getObjectsWithId( $test. '_' . ConversationGenerator::SCENARIO . '_' . 1)->pop();
        $this->assertEquals($test. '_' . ConversationGenerator::SCENARIO . '_' . 1, $first_scenario->getODId());

        $scenarios->filter(function ($scenario) use ($count) {
           $this->assertCount($count, $scenario->getConversations());
           $scenario->getConversations()->filter(function ($conversation) use ($count) {
               $this->assertCount($count, $conversation->getScenes());
               $conversation->getScenes()->filter(function ($scene) use ($count) {
                  $this->assertCount($count, $scene->getTurns());
                  $scene->getTurns()->filter(function ($turn) use ($count) {
                      $this->assertCount($count, $turn->getResponseIntents());
                      $this->assertCount($count, $turn->getRequestIntents());
                  });
               });
           });
        });
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

    public function testIdenticalNamedItemsInCollection()
    {
        // We use Laravel collections to store conversational objects and there are several instances where we may
        // be dealining with objects with the same OD id so must ensure that they don't get overidden.

        $intents = new IntentCollection();
        $intents->addObject(Intent::createIntent('one', 1));
        $intents->addObject(Intent::createIntent('one', 0.5));
        $intents->addObject(Intent::createIntent('one', 0.3));

        $this->assertCount(3, $intents);
    }

}
