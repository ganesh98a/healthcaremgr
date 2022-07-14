<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceStatementAddTotalAmountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {		
		 Schema::table('tbl_finance_statement', function (Blueprint $table){
            if(!Schema::hasColumn('tbl_finance_statement','total')){
                $table->double('total',8, 2)->comment('invoice total ammount inc gst')->after('booked_by');
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
            if(Schema::hasColumn('tbl_finance_statement','total')){
                $table->dropColumn('total');
            }
        });
    }
}
