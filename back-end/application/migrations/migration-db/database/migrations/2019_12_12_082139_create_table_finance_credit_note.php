<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFinanceCreditNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_credit_note')) {
            Schema::create('tbl_finance_credit_note', function (Blueprint $table) {
                $table->increments('id');
                $table->double('total_amount',10,2)->nullable()->comment('tbl_member auto_increment id ');
                $table->string('credit_note_number',255)->nullable()->comment('auto genrate number');
                $table->unsignedInteger('booked_by')->nullable()->comment('tbl_finance_invoice auto_increment id');
                $table->unsignedInteger('credit_note_for')->nullable()->comment('tbl_finance_credit_note auto_increment id');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedSmallInteger('status')->nullable()->comment('1-used');
                $table->unsignedSmallInteger('archive')->default('0');
            });
        }

        if (Schema::hasTable('tbl_finance_credit_note')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_credit_note_before_insert_add_credit_note_number`');
            DB::unprepared("CREATE TRIGGER `tbl_finance_credit_note_before_insert_add_credit_note_number` BEFORE INSERT ON `tbl_finance_credit_note` FOR EACH ROW
            IF NEW.credit_note_number IS NULL or NEW.credit_note_number='' THEN
            SET NEW.credit_note_number = (SELECT CONCAT('CN',(Coalesce((SELECT id FROM tbl_finance_credit_note ORDER BY id DESC LIMIT 1),0) + 1)+10000));
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
        Schema::dropIfExists('tbl_finance_credit_note');
    }
}
