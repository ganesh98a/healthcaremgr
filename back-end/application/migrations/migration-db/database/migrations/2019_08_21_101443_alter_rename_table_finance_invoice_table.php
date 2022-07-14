<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRenameTableFinanceInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_finance_invoice')) {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            Schema::rename('tbl_finance_invoice', 'tbl_plan_management');
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
      if (Schema::hasTable('tbl_plan_management')) {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
            Schema::rename('tbl_plan_management', 'tbl_finance_invoice');
        });
      }
    }
}
