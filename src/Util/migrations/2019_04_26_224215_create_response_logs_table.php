<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponseLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('response_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('http_status');
            $table->decimal('request_length', 12, 6);
            $table->integer('memory_usage');
            $table->text('raw_response');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('response_logs');
    }
}
