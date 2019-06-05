<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\OpeningIntent;
use OpenDialogAi\Core\Attribute\AttributeDoesNotExistException;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\InterpreterEngine\tests\Interpreters\TestNameInterpreter;

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

        $this->registerInterpreter(new TestNameInterpreter());

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

        /* @var UserContext $userContext; */
        $userContext = $this->contextService->createUserContext($utterance);
        $this->assertCount(2, $this->contextService->getContexts());

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
EOT;
    }
}
