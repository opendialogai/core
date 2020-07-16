<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenDialogAi\ConversationBuilder\Conversation;

class AlterStatusInConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $numberOfStatuses = count(scandir(storage_path('statuses')));
        } catch (ErrorException $e) {
            $numberOfStatuses = 0;
        }

        $filename = 'statuses_' . $numberOfStatuses . '_' . date('Y-m-d-H-i-s');
        Artisan::call('statuses:store', [ 'filename' => $filename ]);

        if (DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite' || DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('conversations', function (Blueprint $table) {
                $table->enum('status', ['saved', 'activatable', 'activated', 'deactivated', 'archived'])->default('');
            });
        } else {
            Conversation::all()->each(function (Conversation $conversation) {
                $conversation->status = 'saved';
                $conversation->save(['validate' => false]);
            });

            DB::statement("ALTER TABLE conversations MODIFY status ENUM('saved', 'activatable', 'activated', 'deactivated', 'archived')");
        }

        Artisan::call('statuses:read', [ 'filename' => $filename ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Artisan::call('statuses:store');

        if (DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite' || DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn('status');
                $table->enum('status', ['imported', 'invalid', 'validated', 'published']);
            });
        } else {
            DB::statement("ALTER TABLE conversations MODIFY status ENUM('imported', 'invalid', 'validated', 'published')")->default('');
        }

        Artisan::call('statuses:read --down');
    }
}
