<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class SceneTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    public function setUp(): void
    {
        parent::setUp();
        $this->initDDgraph();

        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot',
            'hello_again_bot' => 'hello_again_bot'
        ]);

        $this->conversationEngine = $this->app->make(ConversationEngineInterface::class);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \OpenDialogAi\ActionEngine\Exceptions\ActionNotAvailableException
     * @throws \OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException
     * @throws \OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported
     */
    public function testScenes()
    {
        $this->publishConversation($this->scene2Conv());
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');

        /* @var UserContext $userContext ; */
        $userContext = ContextService::createUserContext($utterance);
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('hello_human', $intent->getId());
    }

    public function testSingleScene()
    {
        $this->publishConversation($this->singleSceneConv());

        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');

        /* @var UserContext $userContext ; */
        $userContext = ContextService::createUserContext($utterance);
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('hello_human', $intent->getId());

        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_again_bot');

        /* @var UserContext $userContext ; */
        $userContext = ContextService::createUserContext($utterance);
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('hello_again_human', $intent->getId());
    }

    private function scene2Conv()
    {
        return /** @lang yaml */
        <<< EOT
conversation:
  id: scene_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
            scene: scene_2
    scene_2:
      intents:
        - b:
            i: hello_human
EOT;
    }

    private function singleSceneConv()
    {
        return /** @lang yaml */
            <<< EOT
conversation:
  id: scene_conversation
  scenes:
    opening_scene:
      intents:
        - u: 
            i: hello_bot
        - b:
            i: hello_human
        - u:
            i: hello_again_bot
        - b:
            i: hello_again_human
EOT;
    }
}
