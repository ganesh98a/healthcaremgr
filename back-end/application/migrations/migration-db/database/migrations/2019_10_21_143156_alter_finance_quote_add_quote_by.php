<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteAddQuoteBy extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_quote', 'created_by')) {
                $table->unsignedInteger('created_by')->comment('Primary key tbl_member')->after('id');
            }
            if (!Schema::hasColumn('tbl_finance_quote', 'quote_number')) {
                $table->string('quote_number', 255)->after('id');
            }
            if (!Schema::hasColumn('tbl_finance_quote', 'action_by')) {
                $table->unsignedInteger('action_by')->comment('primary key tbl_member')->after('status');
            }
            if (!Schema::hasColumn('tbl_finance_quote', 'action_at')) {
                $table->datetime('action_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('date for Accept, Reject or Archive')->after('action_by');
            }
            if (!Schema::hasColumn('tbl_finance_quote', 'pdf_file')) {
                $table->string('pdf_file', 255)->comment('quote pdf filename')->after('quote_note');
            }
        });
        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_quote_befor_insert_add_quote_number`');
            DB::unprepared("CREATE TRIGGER `tbl_finance_quote_befor_insert_add_quote_number` BEFORE INSERT ON `tbl_finance_quote` FOR EACH ROW
        IF NEW.quote_number IS NULL or NEW.quote_number='' THEN
          SET NEW.quote_number = (SELECT CONCAT('',(COALESCE((SELECT id FROM tbl_finance_quote ORDER BY id DESC LIMIT 1),0) + 1)+100000));
          END IF;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_quote_befor_insert_add_quote_number`');
        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('tbl_finance_quote', 'quote_number')) {
                $table->dropColumn('quote_number');
            }
            if (Schema::hasColumn('tbl_finance_quote', 'action_by')) {
                $table->dropColumn('action_by');
            }
            if (Schema::hasColumn('tbl_finance_quote', 'action_at')) {
                $table->dropColumn('action_at');
            }
            if (Schema::hasColumn('tbl_finance_quote', 'pdf_file')) {
                $table->dropColumn('pdf_file');
            }
        });
    }

}
