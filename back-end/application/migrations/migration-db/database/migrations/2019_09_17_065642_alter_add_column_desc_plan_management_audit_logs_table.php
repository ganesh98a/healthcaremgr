<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnDescPlanManagementAuditLogsTable extends Migration
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
            $table->string('Description',150);
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
       if (Schema::hasTable('tbl_plan_management_audit_logs') && Schema::hasColumn('tbl_plan_management_audit_logs', 'Description')) {
        Schema::table('tbl_plan_management_audit_logs', function (Blueprint $table) {
            $table->dropColumn('Description');
        });
      }
    }
}
