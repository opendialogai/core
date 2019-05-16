<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->text('name')->nullable();
            $table->enum('status', ['imported', 'invalid', 'validated', 'published']);
            $table->enum('yaml_validation_status', ['waiting', 'invalid', 'validated']);
            $table->enum('yaml_schema_validation_status', ['waiting', 'invalid', 'validated']);
            $table->enum('scenes_validation_status', ['waiting', 'invalid', 'validated']);
            $table->enum('model_validation_status', ['waiting', 'invalid', 'validated']);
            $table->text('notes')->nullable();
            $table->longText('model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}
