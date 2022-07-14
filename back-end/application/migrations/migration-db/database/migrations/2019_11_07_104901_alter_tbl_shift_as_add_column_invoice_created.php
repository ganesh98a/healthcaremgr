<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAsAddColumnInvoiceCreated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            $table->unsignedTinyInteger("invoice_created")->after('shift_amendment')->comment('1- when invoice of shift is created');
            $table->unsignedTinyInteger("invoice_payment_status")->after('status')->comment('1-when invoice is paid this column is updated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'invoice_created')) {
                $table->dropColumn('invoice_created');
            } 

            if (Schema::hasColumn('tbl_shift', 'invoice_payment_status')) {
                $table->dropColumn('invoice_payment_status');
            }            
        });
    }
}
