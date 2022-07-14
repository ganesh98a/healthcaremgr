<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceStatementAddStatementNumberAndTriggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
		 Schema::table('tbl_finance_statement', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_statement', 'statement_number')) {
                $table->string('statement_number', 100)->after('id')->nullable()->default(null);
            }
        });

        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_statement_before_insert_add_statementNumber`');
        DB::unprepared("CREATE TRIGGER `tbl_finance_statement_before_insert_add_statementNumber` BEFORE INSERT ON `tbl_finance_statement` FOR EACH ROW
        IF NEW.statement_number IS NULL or NEW.statement_number='' THEN
          SET NEW.statement_number = (SELECT CONCAT('ST',(Coalesce((SELECT id FROM tbl_finance_statement ORDER BY id DESC LIMIT 1),0) + 1)+10000));
          END IF;");
		
		
     
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_statement_before_insert_add_statementNumber`');

        Schema::table('tbl_finance_statement', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_statement', 'statement_number')) {
                $table->dropColumn('statement_number');
            }
        });
    }
}
