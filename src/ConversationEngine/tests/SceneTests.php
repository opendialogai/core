<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class SceneTests extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    /** @var ContextService */
    private $contextService;

    public function setUp(): void
    {
        parent::setUp();
        $this->initDDgraph();

        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot'
        ]);

        $this->conversationEngine = $this->app->make(ConversationEngineInterface::class);
        $this->contextService = $this->app->make(ContextService::class);
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
        $userContext = $this->contextService->createUserContext($utterance);
        $intent = $this->conversationEngine->getNextIntent($userContext, $utterance);

        $this->assertEquals('hello_human', $intent->getId());
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
}
