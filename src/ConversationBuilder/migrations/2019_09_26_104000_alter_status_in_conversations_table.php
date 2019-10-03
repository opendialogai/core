<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterStatusInConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('statuses:store');

        if (DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('conversations', function (Blueprint $table) {
                $table->enum('status', ['saved', 'activatable', 'activated', 'deactivated', 'archived'])->default('');
            });
        } else {
            DB::statement("ALTER TABLE conversations MODIFY status ENUM('saved', 'activatable', 'activated', 'deactivated', 'archived')");
        }

        Artisan::call('statuses:read');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Artisan::call('statuses:store');

        if (DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
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
