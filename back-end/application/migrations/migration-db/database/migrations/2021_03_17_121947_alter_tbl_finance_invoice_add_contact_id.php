<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceAddContactId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_invoice', 'contact_id')) {
                $table->unsignedBigInteger('contact_id')->nullable()->after("account_id")->comment('tbl_person.id');
                $table->foreign('contact_id')->references('id')->on('tbl_person')->onUpdate('cascade')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice', 'contact_id')) {
                $table->dropForeign(['contact_id']);
                $table->dropColumn('contact_id');
            }
        });
    }
}
