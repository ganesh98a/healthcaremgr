<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_finance_audit_logs')) {
        Schema::create('tbl_finance_audit_logs', function (Blueprint $table) {
          $table->increments('id');
          $table->string('category', 50);
          $table->string('user', 50);
          $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_finance_audit_logs');
    }
}
