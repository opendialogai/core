<?php

namespace OpenDialogAi\Core\Console\Commands\Tests;

use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\Core\Tests\TestCase;

class ImportExportConversationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->initDDgraph();
    }

    public function testImportConversation()
    {
        $exitCode = Artisan::call('conversation:import ' . dirname(__FILE__) . '/example_conversation --activate --yes');
        $this->assertEquals(0, $exitCode);

        $conversation = Conversation::where(['name' => 'example_conversation'])->first();
        $this->assertEquals(1, $conversation->id);

        $conversationStore = $this->app->make(ConversationStoreInterface::class);
        $conversation = $conversationStore->getConversationTemplateByUid($conversation->graph_uid);
        $scene = $conversation->getOpeningScenes()->first()->value;
        $intent = $scene->getIntentsSaidByBotInOrder()->first()->value;
        $this->assertEquals('intent.core.NoMatchResponse', $intent->getLabel());
    }

    public function testExportConversation()
    {
        Artisan::call('conversation:import ' . dirname(__FILE__) . '/example_conversation --activate --yes');

        $this->expectOutputRegex('/^.*id: example_conversation.*$/');
        $exitCode = Artisan::call('conversation:export example_conversation');
        $this->assertEquals(0, $exitCode);

        $exitCode = Artisan::call('conversation:export example_conversation -f exported_conversation');
        $this->assertEquals(0, $exitCode);

        $this->assertFileExists('exported_conversation');
        unlink('exported_conversation');
    }
}
