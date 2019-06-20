<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\OpeningIntent;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Attribute\IntAttribute;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\InterpreterEngine\tests\Interpreters\TestAgeInterpreter;
use OpenDialogAi\InterpreterEngine\tests\Interpreters\TestNameInterpreter;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class AttributeExtractionTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    /** @var ContextService */
    private $contextService;

    /** @var OpenDialogController */
    private $odController;

    public function setUp(): void
    {
        parent::setUp();

        $this->registerMultipleInterpreters([new TestNameInterpreter(), new TestAgeInterpreter()]);

        // Add 'age' and 'dob_year' as a known attributes
        $this->setConfigValue('opendialog.context_engine.custom_attributes',
        [
            'age' => IntAttribute::class,
            'dob_year' => IntAttribute::class
        ]);

        $this->conversationEngine = $this->app->make(ConversationEngineInterface::class);
        $this->contextService = $this->app->make(ContextService::class);
        $this->odController = $this->app->make(OpenDialogController::class);


        $this->publishConversation($this->getExampleConversation());
    }

    public function testOpeningSceneCreated()
    {
        $conversationStore = $this->conversationEngine->getConversationStore();
        $openingIntents = $conversationStore->getAllOpeningIntents();

        $this->assertCount(1, $openingIntents);

        /** @var OpeningIntent $myNameIntent */
        $myNameIntent = $openingIntents->first()->value;
        $this->assertEquals('my_name_is', $myNameIntent->getIntentId());

        $expectedAttributes = $myNameIntent->getExpectedAttributes();

        $this->assertCount(2, $expectedAttributes);
        $this->assertContains('user.first_name', $expectedAttributes->toArray());
        $this->assertContains('session.last_name', $expectedAttributes->toArray());
    }

    public function testCorrectIntentReturned()
    {
        $utterance = UtteranceGenerator::generateChatOpenUtterance('my_name_is');

        $this->assertCount(2, $this->contextService->getContexts());
        /* @var UserContext $userContext; */
        $userContext = $this->contextService->createUserContext($utterance);
        $this->assertCount(3, $this->contextService->getContexts());

        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('hello_user', $intent->getLabel());
    }

    public function testAttributeStorage()
    {
        $utterance = UtteranceGenerator::generateChatOpenUtterance('my_name_is');
        $this->odController->runConversation($utterance);

        try {
            // Check that the attributes were stored in the right contexts
            $firstName = $this->contextService->getAttribute('first_name', UserContext::USER_CONTEXT);
            $this->assertEquals('first_name', $firstName->getValue());

            $lastName = $this->contextService->getAttribute('last_name', ContextService::SESSION_CONTEXT);
            $this->assertEquals('last_name', $lastName->getValue());
        } catch (AttributeDoesNotExistException $e) {
            $this->fail('Attribute should exist in the right context');
        }
    }

    public function testFullJourney()
    {
        $outgoingIntent = OutgoingIntent::create(['name' => 'hello_user']);
        MessageTemplate::create([
            'name' => 'name message',
            'message_markup' => '<message><text-message>{user.first_name} {session.last_name}</text-message></message>',
            'outgoing_intent_id' => $outgoingIntent->id
        ]);

        $outgoingIntent = OutgoingIntent::create(['name' => 'age_response']);
        MessageTemplate::create([
            'name' => 'age message',
            'message_markup' => '<message><text-message>age: {user.age}. DOB: {session.dob_year}</text-message></message>',
            'outgoing_intent_id' => $outgoingIntent->id
        ]);

        $utterance1 = UtteranceGenerator::generateChatOpenUtterance('my_name_is');
        $messageWrapper = $this->odController->runConversation($utterance1);

        $this->assertCount(1, $messageWrapper->getMessages());

        /** attributes as set in @see TestNameInterpreter */
        $this->assertEquals('first_name last_name', $messageWrapper->getMessages()[0]->getText());

        // Now make a second utterance to test non opening intents
        $utterance2 = UtteranceGenerator::generateChatOpenUtterance('my_age_is', $utterance1->getUser());
        $messageWrapper = $this->odController->runConversation($utterance2);

        // dob_year was not defined as an expected attribute, so should be saved in session context
        // values come from the TestAgeInterpreter
        $this->assertCount(1, $messageWrapper->getMessages());
        $this->assertEquals('age: 21. DOB: 1994', $messageWrapper->getMessages()[0]->getText());
    }

    private function getExampleConversation()
    {
        return <<<EOT
conversation:
  id: attribute_test_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: my_name_is
            interpreter: interpreter.test.name
            expected_attributes:
                - id: user.first_name
                - id: session.last_name
        - b: 
            i: hello_user
            scene: get_age
    get_age:
      intents:
        - u:
            i: my_age_is
            interpreter: interpreter.test.age
            expected_attributes:
                - id: user.age
        - b:
            i: age_response
EOT;
    }
}
