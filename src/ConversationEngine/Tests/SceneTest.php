<?php

namespace OpenDialogAi\ConversationEngine\Tests;

use GuzzleHttp\Exception\GuzzleException;
use OpenDialogAi\ContextEngine\Contexts\User\CurrentIntentNotSetException;
use OpenDialogAi\ContextEngine\Contexts\User\UserContext;
use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngine;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreatorException;
use OpenDialogAi\Core\Graph\Node\NodeDoesNotExistException;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;
use OpenDialogAi\Core\Utterances\Exceptions\FieldNotSupported;

class SceneTest extends TestCase
{
    /* @var ConversationEngine */
    private $conversationEngine;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSupportedCallbacks([
            'hello_bot' => 'hello_bot',
            'hello_again_bot' => 'hello_again_bot'
        ]);

        $this->conversationEngine = resolve(ConversationEngineInterface::class);
    }

    /**
     * @requires DGRAPH
     *
     * @throws FieldNotSupported
     * @throws GuzzleException
     * @throws NodeDoesNotExistException
     * @throws CurrentIntentNotSetException
     * @throws EIModelCreatorException
     */
    public function testScenes()
    {
        $this->activateConversation($this->scene2Conv());
        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');

        /* @var UserContext $userContext ; */
        $userContext = ContextService::createUserContext($utterance);
        list($intent) = $this->conversationEngine->getNextIntents($userContext, $utterance);

        $this->assertEquals('hello_human', $intent->getId());
    }

    /**
     * @requires DGRAPH
     */
    public function testSingleScene()
    {
        $this->activateConversation($this->singleSceneConv());

        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_bot');

        /* @var UserContext $userContext ; */
        $userContext = ContextService::createUserContext($utterance);
        list($intent) = $this->conversationEngine->getNextIntents($userContext, $utterance);

        $this->assertEquals('hello_human', $intent->getId());

        $utterance = UtteranceGenerator::generateChatOpenUtterance('hello_again_bot', $utterance->getUser());

        /* @var UserContext $userContext ; */
        $userContext = ContextService::createUserContext($utterance);
        list($intent) = $this->conversationEngine->getNextIntents($userContext, $utterance);

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
