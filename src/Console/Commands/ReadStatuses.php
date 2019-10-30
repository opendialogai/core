<?php

namespace OpenDialogAi\Core\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ConversationBuilder\Conversation;

class ReadStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statuses:read {--d|down} {filename?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads old status names from a CSV file and updates the database.';

    private $statusMapUp = [
        'imported' => \OpenDialogAi\Core\Conversation\Conversation::SAVED,
        'invalid' => \OpenDialogAi\Core\Conversation\Conversation::SAVED,
        'validated' => \OpenDialogAi\Core\Conversation\Conversation::ACTIVATABLE,
        'published' => \OpenDialogAi\Core\Conversation\Conversation::ACTIVATED
    ];

    private $statusMapDown = [
        \OpenDialogAi\Core\Conversation\Conversation::SAVED => 'imported',
        \OpenDialogAi\Core\Conversation\Conversation::ACTIVATABLE => 'validated',
        \OpenDialogAi\Core\Conversation\Conversation::ACTIVATED => 'published'
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Reading statuses");

        $filenameArg = $this->argument('filename');
        if ($filenameArg) {
            $filename = $filenameArg . '.csv';
        } else {
            $dir = scandir(storage_path('statuses'), SCANDIR_SORT_DESCENDING);
            $filename = $dir[0];
        }

        // Read the CSV file
        $fullFilePath = storage_path('statuses/' . $filename);
        $csv = fopen($fullFilePath, 'r');

        $data = [];
        while ($row = fgetcsv($csv)) {
            $data[] = $row;
        }

        fclose($csv);
        unlink($fullFilePath);

        // Update statuses in database
        foreach ($data as $update) {
            /** @var Conversation $conversation */
            $conversation = Conversation::where('id', $update[0])->first();
            $conversation->status = $this->mapStatus($update[1]);
            $conversation->save(["validate" => false]);
        }

        return;
    }

    private function mapStatus($oldStatus): string
    {
        if ($this->option("down")) {
            return $this->mapStatusDown($oldStatus);
        } else {
            return $this->mapStatusUp($oldStatus);
        }
    }

    private function mapStatusUp($oldStatus): string
    {
        if (key_exists($oldStatus, $this->statusMapUp)) {
            return $this->statusMapUp[$oldStatus];
        } else {
            return $oldStatus;
        }
    }

    private function mapStatusDown($oldStatus): string
    {
        if (key_exists($oldStatus, $this->statusMapDown)) {
            return $this->statusMapDown[$oldStatus];
        } else {
            return 'imported';
        }
    }
}
