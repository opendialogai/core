<?php

namespace OpenDialogAi\ConversationLog\Tests;

use OpenDialogAi\ConversationLog\Message;
use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Tests\TestCase;
use OpenDialogAi\Core\Tests\Utils\UtteranceGenerator;

class ConversationLogFieldsTest extends TestCase
{
    /**
     * @group skip
     */
    public function testMessageFields()
    {
        $utterance = UtteranceGenerator::generateTextUtterance('hello?');

        resolve(OpenDialogController::class)->runConversation($utterance);
        $this->assertCount(2, Message::all());

        $this->assertEquals('intent.core.NoMatch', Message::where('author', $utterance->getUser()->getId())->first()->intents[0]);
        $this->assertEquals('no_match_conversation', Message::where('author', $utterance->getUser()->getId())->first()->conversation);
        $this->assertEquals('opening_scene', Message::where('author', $utterance->getUser()->getId())->first()->scene);

        $this->assertEquals('intent.core.NoMatchResponse', Message::where('author', '<>', $utterance->getUser()->getId())->first()->intents[0]);
        $this->assertEquals('no_match_conversation', Message::where('author', '<>', $utterance->getUser()->getId())->first()->conversation);
        $this->assertEquals('opening_scene', Message::where('author', '<>', $utterance->getUser()->getId())->first()->scene);
    }
}
