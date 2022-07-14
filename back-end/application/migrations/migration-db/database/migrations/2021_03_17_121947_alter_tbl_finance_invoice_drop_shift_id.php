<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceDropShiftId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }

            if (!Schema::hasColumn('tbl_finance_invoice', 'invoice_date')) {
                $table->dateTime('invoice_date')->nullable()->after("account_id");
            }
            if (!Schema::hasColumn('tbl_finance_invoice', 'invoice_type')) {
                $table->unsignedInteger('invoice_type')->default(1)->comment('1 = B2B Invoice')->after("invoice_date");
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
            if (Schema::hasColumn('tbl_finance_invoice', 'invoice_date')) {
                $table->dropColumn('invoice_date');
            }
            if (Schema::hasColumn('tbl_finance_invoice', 'invoice_type')) {
                $table->dropColumn('invoice_type');
            }
        });
    }
}
