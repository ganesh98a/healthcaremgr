<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateS3MigrationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_s3_migration_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('module_id')->nullable()->default(0)->comment('1 - Recruitment / 2 - Member');
            $table->unsignedInteger('status')->nullable()->default(0)->comment('1 - success / 0 - failed');
            $table->string('migration_step', 128)->nullable()->comment('file copied / comparision');
            $table->text('message')->nullable()->comment('Status of property ID');
            $table->unsignedInteger('draft_contract_type')->nullable()->default(0)->comment('1 - cabday / 2 - group interview');
            $table->unsignedInteger('applicant_id')->nullable();
            $table->unsignedInteger('property_id')->nullable();
            $table->unsignedInteger('server_file_size')->nullable();
            $table->unsignedInteger('s3_file_size')->nullable();
            $table->string('file_name', 128)->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_s3_migration_log');
    }
}
