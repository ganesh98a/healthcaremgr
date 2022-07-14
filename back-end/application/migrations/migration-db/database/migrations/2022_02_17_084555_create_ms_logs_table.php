<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_ms_error_logs')) {
            Schema::create('tbl_ms_error_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('module_id')->nullable()->comment('1 - Group booking / 2 - Application');
                $table->string('title', 128)->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            });
         }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ms_error_logs');
    }
}
