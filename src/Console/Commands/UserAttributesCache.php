<?php

namespace OpenDialogAi\Core\Console\Commands;

use Illuminate\Console\Command;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\Core\Attribute\AttributeInterface;
use OpenDialogAi\Core\Conversation\ChatbotUser;
use OpenDialogAi\Core\Conversation\UserAttribute;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
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

        /** @var UserService $userService */
        $userService = app()->make(UserService::class);

        $query = $userService->getUsersQuery();
        $results = $dgraph->query($query);

        foreach ($results->getData() as $userData) {
            /** @var ChatbotUser $user */
            $user = $userService->createChatbotUserFromResponseData($userData);

            if ($user->getUserAttributeValue('last_seen') > $lastRunTime) {
                // clear existing data
                UserAttributes::where('user_id', $user->getId())->each(function (UserAttributes $userStat) {
                    $userStat->delete();
                });

                /**
                 * @var string $stat
                 * @var AttributeInterface $value
                 */
                foreach ($user->getAttributes() as $stat => $value) {
                    UserAttributes::create([
                        'user_id'   => $user->getId(),
                        'attribute' => $stat,
                        'value'     => $value->getValue()
                    ]);
                }

                /**
                 * @var string $stat
                 * @var UserAttribute $value
                 */
                foreach ($user->getAllUserAttributes() as $stat => $value) {
                    UserAttributes::create([
                        'user_id'   => $user->getId(),
                        'attribute' => $stat,
                        'value'     => $value->getInternalAttribute()->getValue()
                    ]);
                }
            }
        }

        StatsRuns::create();
    }
}
