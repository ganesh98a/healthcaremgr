<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumunExportExcelPlanManagementSyncLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_plan_management_sync_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_plan_management_sync_logs','export_csv') ) {
              $table->string('export_csv',100)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_plan_management_sync_logs', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_plan_management_sync_logs','export_csv')) {
            $table->dropColumn('export_csv');
          }
        });
    }
}
