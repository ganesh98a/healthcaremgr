<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblPlanManagementSyncLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_plan_management_sync_logs')) {
          Schema::create('tbl_plan_management_sync_logs', function (Blueprint $table) {
              $table->increments('id');
              $table->dateTime('date_of_upload')->default(DB::raw('CURRENT_TIMESTAMP'));
              $table->dateTime('date_of_complete')->default('0000-00-00 00:00:00');
              $table->unsignedInteger('no_of_records_to_followup');
              $table->unsignedInteger('no_of_records_to_xero');
              $table->unsignedInteger('total_record_in_csv');
              $table->string('file_path',100);
              $table->string('action',100);
              $table->unsignedSmallInteger('status')->comment('1-Successful , 2-Failed, 3-Processing	');
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
        Schema::dropIfExists('tbl_plan_management_sync_logs');
    }
}
