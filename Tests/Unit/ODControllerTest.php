<?php

namespace OpenDialogAi\Core\Tests\Unit;

use OpenDialogAi\Core\Controllers\OpenDialogController;
use OpenDialogAi\Core\Conversation\ConversationDataClient;
use OpenDialogAi\Core\Tests\TestCase;
use Illuminate\Support\Facades\Http;


class ODControllerTest extends TestCase
{
    public function testBinding()
    {
        $this->assertEquals(OpenDialogController::class, get_class($this->app->make(OpenDialogController::class)));
    }

    /**
     * @group skip
     */
    public function testODTemp()
    {
        $od = $this->app->make(OpenDialogController::class);

        $utterance = $this->createWebchatMessageUtteranceAttribute();
        $od->runConversation($utterance);

    }

    /**
     * @group skip
     */
    public function testConnectToDgraph()
    {
        $dataClient = $this->app->make(ConversationDataClient::class);
        dump($dataClient->query());

    }

    //@todo Add OD Controller tests.
}
