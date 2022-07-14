<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRenameFinanceAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_audit_logs')) {
        Schema::table('tbl_finance_audit_logs', function (Blueprint $table) {
            Schema::rename('tbl_finance_audit_logs', 'tbl_plan_management_audit_logs');
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
      if (Schema::hasTable('tbl_plan_management_audit_logs')) {
        Schema::table('tbl_finance_audit_logs', function (Blueprint $table) {
            Schema::rename('tbl_plan_management_audit_logs', 'tbl_finance_audit_logs');
        });
      }
    }
}
