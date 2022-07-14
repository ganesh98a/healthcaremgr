<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFinanceNdisInvoiceExportLoges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_invoice_ndis_export_log')) {
            Schema::create('tbl_finance_invoice_ndis_export_log', function (Blueprint $table) {
              $table->increments('id');
              $table->string('file_name',255)->nullable();
              $table->integer('created_by')->default(0)->comment('tbl_member auto_increment id ');
              $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
              $table->dateTime('created')->default('0000-00-00 00:00:00');

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
        Schema::dropIfExists('tbl_finance_invoice_ndis_export_log');
    }
}
