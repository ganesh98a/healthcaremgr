<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateS3LogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_s3_logs')) {
            Schema::create('tbl_s3_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('module_id')->nullable()->comment('1 - Recruitment / 2 - Member');
                $table->string('title', 128)->nullable();
                $table->text('log_type')->nullable()->comment('init/success/failure');
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
        Schema::dropIfExists('tbl_s3_logs');
    }
}
