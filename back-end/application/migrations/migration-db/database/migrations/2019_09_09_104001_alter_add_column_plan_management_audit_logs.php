<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnPlanManagementAuditLogs extends Migration
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
              $table->unsignedInteger('participant_id');
              $table->unsignedInteger('plan_id');
              $table->unsignedInteger('action')->comment("1=Moved 2=Manual 3=Export  4=Import  5=Biller Added  6=Upload Invoice -Failure  7=Upload Invoice -Success  8=Invoice Sent  9=Insufficient Funds  10=Sync Logs  11=Dispute Added  12=Dispute Resolved  13=Dispute Edited   14=New Member Access ");
              $table->dropColumn('category');
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
      if(Schema::hasTable('tbl_plan_management_audit_logs') && Schema::hasColumn('tbl_plan_management_audit_logs', 'plan_id') && Schema::hasColumn('tbl_plan_management_audit_logs', 'category') && Schema::hasColumn('tbl_plan_management_audit_logs', 'action')&& Schema::hasColumn('tbl_plan_management_audit_logs', 'participant_id')) {
        Schema::table('tbl_plan_management_audit_logs', function (Blueprint $table) {
          $table->dropColumn('participant_id');
          $table->dropColumn('plan_id');
          $table->dropColumn('action');
          $table->string('category',50);
        });
      }
    }
}
