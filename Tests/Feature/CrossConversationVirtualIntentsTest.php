<?php

namespace OpenDialogAi\Core\Tests\Feature;

use OpenDialogAi\ContextEngine\Facades\ContextService;
use OpenDialogAi\ConversationEngine\ConversationEngineInterface;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class CrossConversationVirtualIntentsTest extends TestCase
{
    /**
     * @requires DGRAPH
     * @group skip
     */
    public function testUVirtualBetweenConversations()
    {
        $this->activateConversation($this->getStartConversationMarkup());
        $this->activateConversation($this->getFollowUpConversationMarkUp());

        $user = UtteranceGenerator::generateUser();
        $startUtterance = UtteranceGenerator::generateChatOpenUtterance('start_1', $user);
        $userContext = ContextService::createUserContext($startUtterance);

        $conversationEngine = resolve(ConversationEngineInterface::class);
        $intents = $conversationEngine->getNextIntents($userContext, $startUtterance);

        $this->assertCount(2, $intents);
        $this->assertEquals('end_1', $intents[0]->getId());
        $this->assertEquals('continue_b', $intents[1]->getId());

        // Now make sure the second conversation still operates as expected
        $nextUtterance = UtteranceGenerator::generateChatOpenUtterance('continue_u', $user);
        $intents = $conversationEngine->getNextIntents($userContext, $nextUtterance);
        $this->assertCount(1, $intents);
        $this->assertEquals('end_2', $intents[0]->getId());
    }

    private function getStartConversationMarkup()
    {
        /** @lang yaml */
        return <<<EOT
conversation:
  id: conversation1
  scenes:
    opening_scene:
      intents:
        - u:
            i: start_1
        - b:
            i: end_1
            u_virtual:
              i: start_2
            completes: true
EOT;
    }

    public function getFollowUpConversationMarkUp()
    {
        /** @lang yaml */
        return <<<EOT
conversation:
  id: conversation2
  scenes:
    opening_scene:
      intents:
        - u:
            i: start_2
        - b:
            i: continue_b
            scene: scene_2
    scene_2:
      intents:
        - u:
            i: continue_u
        - b:
            i: end_2
            completes: true
EOT;
    }
}
