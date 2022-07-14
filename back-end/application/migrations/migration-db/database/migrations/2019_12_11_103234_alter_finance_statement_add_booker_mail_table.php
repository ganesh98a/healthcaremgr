<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceStatementAddBookerMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('tbl_finance_statement', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_finance_statement','booker_mail')){
                $table->smallInteger('booker_mail')->after('booked_by')->comment('0-not send mail to booker /1 - send mail booker/org/sub');
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
       Schema::table('tbl_finance_statement', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_finance_statement','booker_mail')){
                $table->dropColumn('booker_mail');
            }
        });
    }
}
