<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceManualInvoiceAsUpdateComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_manual_invoice')) {
            Schema::table('tbl_finance_manual_invoice', function (Blueprint $table) {
                $table->string('invoice_id', 200)->nullable()->after('id');
                $table->unsignedInteger('invoice_for')->nullable()->after('manual_invoice_notes')->comment('id of participant,Org,Site etc');
                $table->unsignedInteger('booked_by')->nullable()->after('manual_invoice_notes')->comment('1= Site / Home, 2= Participant, 3 =Location');
                $table->date('invoice_shift_start_date')->default('0000-00-00')->change();
                $table->date('invoice_shift_end_date')->default('0000-00-00')->change();
                $table->date('invoice_date')->change()->default('0000-00-00 00:00:00');

                if (Schema::hasColumn('tbl_finance_manual_invoice','status')) {
                    $table->unsignedSmallInteger('status')->unsigned()->comment('0-Draft, 1-Sent, 2-Sent & Read, 3-Error sending, 4-Resend, 5-Dispute, 6-Paid')->change();
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
        if (Schema::hasTable('tbl_finance_manual_invoice')) {
            Schema::table('tbl_finance_manual_invoice', function (Blueprint $table) {

                $table->datetime('invoice_shift_start_date')->change();
                $table->datetime('invoice_shift_end_date')->change();
                $table->datetime('invoice_date')->change();
                
                if (Schema::hasColumn('tbl_finance_manual_invoice','status')) {
                    $table->unsignedSmallInteger('status')->unsigned()->comment('0 - created/ 1 - Yes')->change();
                }

                if (Schema::hasColumn('tbl_finance_manual_invoice', 'invoice_id')) {
                    $table->dropColumn('invoice_id');
                }

                if (Schema::hasColumn('tbl_finance_manual_invoice', 'invoice_for')) {
                    $table->dropColumn('invoice_for');
                }

                if (Schema::hasColumn('tbl_finance_manual_invoice', 'booked_by')) {
                    $table->dropColumn('booked_by');
                }
            });
        }
    }
}
