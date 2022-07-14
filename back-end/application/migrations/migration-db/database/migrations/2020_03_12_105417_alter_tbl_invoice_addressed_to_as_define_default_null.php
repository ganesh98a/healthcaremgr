<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblInvoiceAddressedToAsDefineDefaultNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_invoice_addressed_to')) {
            Schema::table('tbl_invoice_addressed_to', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_invoice_addressed_to', 'firstname')) {
                    $table->string('firstname',50)->nullable()->change();
                }

                if (Schema::hasColumn('tbl_invoice_addressed_to', 'lastname')) {
                    $table->string('lastname',50)->nullable()->change();
                }
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
        Schema::table('tbl_invoice_addressed_to', function (Blueprint $table) {
            //
        });
    }
}
