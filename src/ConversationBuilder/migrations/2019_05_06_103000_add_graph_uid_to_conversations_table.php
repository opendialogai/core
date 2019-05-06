<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphQueries\ConversationQueryFactory;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

class AddGraphUidToConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('graph_uid')->nullable();
        });

        $this->updateGraphUid();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('graph_uid');
        });
    }

    private function updateGraphUid()
    {
        $dGraph = new DGraphClient(env('DGRAPH_URL'), env('DGRAPH_PORT'));

        $rows = DB::table('conversations')->get(['id', 'name', 'status']);
        foreach ($rows as $row) {
            if ($row->status == 'published') {
                $uid = ConversationQueryFactory::getConversationUid($row->name, $dGraph);

                DB::table('conversations')
                    ->where('id', $row->id)
                    ->update(['graph_uid' => $uid]);
            }
        }
    }
}
