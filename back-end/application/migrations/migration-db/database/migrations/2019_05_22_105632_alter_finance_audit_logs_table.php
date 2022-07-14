<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceAuditLogsTable extends Migration
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
            $table->text('title');
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
      if (Schema::hasTable('tbl_finance_audit_logs') && Schema::hasColumn('tbl_finance_audit_logs','title')) {
        Schema::table('tbl_finance_audit_logs', function (Blueprint $table) {
            $table->dropColumn('title');
        });
      }
    }
}
