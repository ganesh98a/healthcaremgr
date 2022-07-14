<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDescriptionColumnSizePlanMgmtAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_plan_management_audit_logs')) {
        Schema::table('tbl_plan_management_audit_logs', function (Blueprint $table) {
          DB::unprepared("ALTER TABLE `tbl_plan_management_audit_logs`
              CHANGE `Description` `Description` LONGTEXT NOT NULL  AFTER `action`
            ");
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
        Schema::table('tbl_plan_management_audit_logs', function (Blueprint $table) {
            //
        });
    }
}
