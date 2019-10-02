<?php

namespace OpenDialogAi\Core\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\Core\Conversation\Model;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\Core\Graph\DGraph\DGraphQuery;
use OpenDialogAi\Core\StatsRuns;
use OpenDialogAi\Core\UserAttributes;

class UserAttributesCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attributes:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dumps the all user attributes from DGraph';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lastRunTime = StatsRuns::all()->last() ? StatsRuns::all()->last()->created_at->timestamp : 0;

        /** @var DGraphClient $dgraph */
        $dgraph = app()->make(DGraphClient::class);

        $query = new DGraphQuery();
        $query->eq(Model::EI_TYPE, Model::CHATBOT_USER)
            ->setQueryGraph([
                Model::UID,
                Model::ID,
                Model::LAST_SEEN
            ]);

        $results = $dgraph->query($query);

        foreach ($results->getData() as $user) {
            if ($user['last_seen'] > $lastRunTime) {
                // clear existing data
                UserAttributes::where('user_id', $user['id'])->each(function (UserAttributes $userStat) {
                    $userStat->delete();
                });

                foreach ($user as $stat => $value) {
                    UserAttributes::create([
                        'user_id'   => $user['id'],
                        'attribute' => $stat,
                        'value'     => $value
                    ]);
                }
            }
        }

        StatsRuns::create();
    }
}
