<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTiggerFinanceCreditNoteInvoiceAttached extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_credit_note_invoice_attached')) {
            Schema::table('tbl_finance_credit_note_invoice_attached', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_credit_note_invoice_attached','refund_number')){
                    $table->string('refund_number',255)->nullable()->comment('refund_number auto genrate number')->after("id");
                }
            });

        }
        if (Schema::hasTable('tbl_finance_credit_note_invoice_attached')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `finance_credit_note_invoice_attached_before_insert_refund_number`');
            DB::unprepared("CREATE TRIGGER `finance_credit_note_invoice_attached_before_insert_refund_number` BEFORE INSERT ON `tbl_finance_credit_note_invoice_attached` FOR EACH ROW
            IF NEW.refund_number IS NULL or NEW.refund_number='' THEN
            SET NEW.refund_number = (SELECT CONCAT('R',(Coalesce((SELECT id FROM tbl_finance_credit_note_invoice_attached ORDER BY id DESC LIMIT 1),0) + 1)+10000));
            END IF;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS `finance_credit_note_invoice_attached_before_insert_refund_number`');
        if (Schema::hasTable('tbl_finance_credit_note_invoice_attached')) {
            Schema::table('tbl_finance_credit_note_invoice_attached', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_credit_note_invoice_attached','refund_number')){
                    $table->dropColumn('refund_number');
                }
            });
        }
    }
}
