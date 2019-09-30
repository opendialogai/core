<?php


namespace OpenDialogAi\Core\Console\Commands\tests;


use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Core\Tests\TestCase;

class TestStoreStatuses extends TestCase
{
    public function test()
    {
        Artisan::call('statuses:store');

        $dir = scandir(storage_path('statuses'), SCANDIR_SORT_DESCENDING);

        $csv = fopen($dir[0], 'r');
        $data = fgetcsv($csv);

        var_dump($data);
    }
}
