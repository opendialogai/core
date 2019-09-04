<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;

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
        $rows = DB::table('conversations')->get(['id', 'name', 'status']);
        foreach ($rows as $row) {
            if ($row->status == 'published') {
                /* @var EIModelConversation $conversationModel */
                $conversationModel = DGraphConversationQueryFactory::getConversationTemplateUid($row->name);

                $uid = $conversationModel->getUid();

                DB::table('conversations')
                    ->where('id', $row->id)
                    ->update(['graph_uid' => $uid]);
            }
        }
    }
}
