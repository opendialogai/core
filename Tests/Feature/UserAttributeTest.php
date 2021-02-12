<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\AttributeEngine\Attributes\IntAttribute;
use OpenDialogAi\AttributeEngine\Attributes\StringAttribute;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Tests\Bot\Actions\TestAction;
use OpenDialogAi\Core\Tests\Bot\Interpreters\TestInterpreter;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\MessageBuilder\MessageMarkUpGenerator;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

class UserAttributeTest extends TestCase
{
    /**
     * @requires DGRAPH
     * @group skip
     */
    public function testUserAttributes()
    {
        $utterance = UtteranceGenerator::generateChatOpenUtterance('WELCOME');
        $this->activateConversation($this->noMatchConversation());

        resolve(OpenDialogController::class)->runConversation($utterance);

        $this->assertTrue(ContextService::getUserContext()->getUser()->hasAttribute(Model::EI_TYPE));
        $this->assertTrue(ContextService::getUserContext()->getUser()->hasUserAttribute('first_name'));
    }

    /**
     * @requires DGRAPH
     * @group skip
     */
    public function testUserCustomAttributes()
    {
        $this->registerSingleInterpreter(new TestInterpreter());

        $this->registerSingleAction(new TestAction());

        $this->setCustomAttributes(
            [
                'intent_test' => StringAttribute::class,
                'action_test' => IntAttribute::class
            ]
        );

        $this->activateConversation($this->getConversation());

        /** @var OutgoingIntent $intent */
        $intent = OutgoingIntent::create(['name' => 'intent.test.hello_user']);

        $markUp = (new MessageMarkUpGenerator())->addTextMessage('Result: {user.intent_test} {user.action_test}');

        $messageTemplate = MessageTemplate::create(
            [
                'name' => 'Test message',
                'message_markup' => $markUp->getMarkUp(),
                'outgoing_intent_id' => $intent->id
            ]
        );

        $intent->messageTemplates()->save($messageTemplate);

        $utterance = UtteranceGenerator::generateTextUtterance('Hello');
        $messages = resolve(OpenDialogController::class)->runConversation($utterance);
        $this->assertCount(1, $messages->getMessages());
        $this->assertEquals('Result: test 1', $messages->getMessages()[0]->getText());
    }

    private function getConversation()
    {
        return <<<EOT
conversation:
  id: hello_bot
  scenes:
    opening_scene:
      intents:
        - u:
            i: intent.test.hello_bot
            interpreter: interpreter.test.hello_bot
            action: action.test.test
            expected_attributes:
              - id: user.intent_test
        - b:
            i: intent.test.hello_user
            completes: true
EOT;
    }
}
