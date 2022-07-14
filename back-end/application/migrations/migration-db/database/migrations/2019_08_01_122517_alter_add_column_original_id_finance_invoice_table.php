<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnOriginalIdFinanceInvoiceTable extends Migration
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
          $table->unsignedInteger('original_id')->nullable();
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
      if(Schema::hasTable('tbl_finance_invoice') && Schema::hasColumn('tbl_finance_invoice', 'original_id') ) {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            $table->dropColumn('original_id');
        });
      }
    }
}
