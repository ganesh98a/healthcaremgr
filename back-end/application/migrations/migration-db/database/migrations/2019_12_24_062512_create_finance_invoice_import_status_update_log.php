<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceInvoiceImportStatusUpdateLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_invoice_import_status_update_log')) {
            Schema::create('tbl_finance_invoice_import_status_update_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('invoice_id');
                $table->text('invoice_data')->nullable();
                $table->unsignedSmallInteger('log_status')->default('0')->comment('1-complete,2-error');
                $table->longText('log_response')->nullable();
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default('0');

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
        Schema::dropIfExists('tbl_finance_invoice_import_status_update_log');
    }
}
