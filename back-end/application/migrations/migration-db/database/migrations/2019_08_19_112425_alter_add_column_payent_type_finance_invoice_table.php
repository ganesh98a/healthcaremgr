<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnPayentTypeFinanceInvoiceTable extends Migration
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
            $table->unsignedInteger('payment_type')->default(0)->comment('1=Automated 0=Manual');
            $table->unsignedInteger('load_status')->default(0)->comment('1=Loaded 0=Not');
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
      if(Schema::hasTable('tbl_finance_invoice') && Schema::hasColumn('tbl_finance_invoice', 'payment_type') && Schema::hasColumn('tbl_finance_invoice', 'load_status')) {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            $table->dropColumn('payment_type');
            $table->dropColumn('load_status');
        });
      }
    }
}
