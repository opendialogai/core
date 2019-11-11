<?php

namespace OpenDialogAi\Core\Console\Commands\tests;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\Core\Tests\TestCase;

class TestReadingAndStoringStatuses extends TestCase
{
    private $dirName;

    public function setUp(): void {
        parent::setUp();
        $this->dirName = storage_path('statuses');
        $this->clearStatusDir();
    }

    public function tearDown(): void {
        $this->clearStatusDir();
        parent::tearDown();
    }

    public function testStoring()
    {
        Artisan::call('statuses:store');
        $dir = scandir($this->dirName, SCANDIR_SORT_DESCENDING);
        $this->assertNull($this->getStatusesFromStorage($dir));

        $this->activateConversation($this->conversation1());
        $this->activateConversation($this->conversation2());

        /** @var Conversation $conv2 */
        $conv2 = Conversation::where('name', 'hello_bot_world2')->first();
        $conv2->deactivateConversation();

        $this->activateConversation($this->conversation3());

        Artisan::call('statuses:store');
        $dir = scandir($this->dirName, SCANDIR_SORT_DESCENDING);
        $data = $this->getStatusesFromStorage($dir);

        $this->assertCount(3, $data);
        $this->assertEquals('activated', $data[0][1]);
        $this->assertEquals('deactivated', $data[1][1]);
        $this->assertEquals('activated', $data[2][1]);
    }

    public function testReading()
    {
        $this->activateConversation($this->conversation1());
        $this->activateConversation($this->conversation2());

        /** @var Conversation $conv2 */
        $conv2 = Conversation::where('name', 'hello_bot_world2')->first();
        $conv2->deactivateConversation();

        $this->activateConversation($this->conversation3());

        Artisan::call('statuses:store');

        $conv2->activateConversation();

        Artisan::call('statuses:read');

        $conv2 = Conversation::where('name', 'hello_bot_world2')->first();
        $this->assertEquals('deactivated', $conv2->status);
    }

    public function testReadingDown()
    {
        $this->activateConversation($this->conversation1());
        $this->activateConversation($this->conversation2());

        /** @var Conversation $conv2 */
        $conv2 = Conversation::where('name', 'hello_bot_world2')->first();
        $conv2->deactivateConversation();

        $this->activateConversation($this->conversation3());

        Artisan::call('statuses:store');

        $conv2->activateConversation($conv2);

        $caught = false;
        try {
            Artisan::call('statuses:read --down');
        } catch (QueryException $e) {
            // Expect to have an integrity constraint violation for the ENUM as the old statuses don't exist in this DB
            $caught = true;
        }
        $this->assertTrue($caught);
    }

    /**
     * @return array
     */
    private function getStatusesFromStorage($dir): ?array
    {
        $csv = fopen(storage_path('statuses/' . $dir[0]), 'r');

        $data = [];
        while ($row = fgetcsv($csv)) {
            $data[] = $row;
        }

        fclose($csv);

        return $data ?: null;
    }

    private function clearStatusDir()
    {
        try {
            $dir = scandir($this->dirName, SCANDIR_SORT_DESCENDING);

            foreach ($dir as $file) {
                if (substr($file, 0, 8) == "statuses") {
                    unlink(storage_path('statuses/' . $file));
                }
            }
        } catch (\Exception $e) {  }
    }
}
