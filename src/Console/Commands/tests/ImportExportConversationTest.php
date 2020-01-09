<?php

namespace OpenDialogAi\Core\Console\Commands\tests;

use Illuminate\Support\Facades\Artisan;
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
    }

    public function testExportConversation()
    {
        Artisan::call('conversation:import ' . dirname(__FILE__) . '/example_conversation --activate --yes');

        $this->expectOutputRegex('/^.*id: example_conversation.*$/');
        $exitCode = Artisan::call('conversation:export example_conversation');
        $this->assertEquals(0, $exitCode);
    }
}
