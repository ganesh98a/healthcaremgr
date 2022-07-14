<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceInvoiceTableUpdateStatusComment extends Migration
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
                if(Schema::hasColumn('tbl_finance_invoice','status')){
                    $table->unsignedSmallInteger('status')->default('0')->comment('0-Payment Pending/Xero Draft,1-Payment Received/Xero Paid,2-Payment Not Received/Xero Not Paid')->change();
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
        if (Schema::hasTable('tbl_finance_invoice')) {
            Schema::table('tbl_finance_invoice', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_invoice','status')){
                    $table->unsignedSmallInteger('status')->comment('0-Draft, 1-Sent, 2-Sent & Read, 3-Error sending, 4-Resend, 5-Dispute, 6-Paid')->change();
                }
            });

        }
    }
}
