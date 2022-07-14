<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceAddCancelColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_invoice', 'cancel_void_reason_id')) {
                $table->unsignedInteger('cancel_void_reason_id')->nullable()->after("contact_id")->comment('tbl_references.id');
                $table->foreign('cancel_void_reason_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->text('cancel_void_reason_notes')->nullable()->after("cancel_void_reason_id");
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
            if (Schema::hasColumn('tbl_finance_invoice', 'cancel_void_reason_id')) {
                $table->dropForeign(['cancel_void_reason_id']);
                $table->dropColumn('cancel_void_reason_id');
            }

            if (Schema::hasColumn('tbl_finance_invoice', 'cancel_void_reason_notes')) {
                $table->dropColumn('cancel_void_reason_notes');
            }
        });
    }
}
