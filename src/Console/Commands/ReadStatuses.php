<?php

namespace OpenDialogAi\Core\Console\Commands;

use Illuminate\Console\Command;

class ReadStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statuses:read';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads old status names from a CSV file and updates the database.';

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
        // Read the CSV file

        // Update statuses in database

    }
}
