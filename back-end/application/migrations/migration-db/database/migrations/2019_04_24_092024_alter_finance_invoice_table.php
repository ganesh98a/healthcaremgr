<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceInvoiceTable extends Migration
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
              $table->string('pdf_url',250);
              $table->string('html_url',250);
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
         if (Schema::hasTable('tbl_finance_invoice') && Schema::hasColumn('tbl_finance_invoice','pdf_url') && Schema::hasColumn('tbl_finance_invoice','html_url')) {
            Schema::table('tbl_finance_invoice', function (Blueprint $table) {
                $table->dropColumn('pdf_url');
                $table->dropColumn('html_url');
            });
        }
    }
}
