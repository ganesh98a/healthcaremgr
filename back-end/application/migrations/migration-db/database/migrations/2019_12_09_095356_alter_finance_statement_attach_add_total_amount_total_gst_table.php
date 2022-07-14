<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceStatementAttachAddTotalAmountTotalGstTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('tbl_finance_statement_attach', function (Blueprint $table){
            if(!Schema::hasColumn('tbl_finance_statement_attach','total')){
                $table->double('total',8, 2)->comment('invoice total ammount incl gst')->after('invoice_id');
            }
			if(!Schema::hasColumn('tbl_finance_statement_attach','gst')){
                $table->double('gst',8, 2)->comment('invoice gst ammount')->after('total');
            }
			if(!Schema::hasColumn('tbl_finance_statement_attach','sub_total')){
                $table->double('sub_total',8, 2)->comment('invoice sub_total ammount ex gst')->after('gst');
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
		 Schema::table('tbl_finance_statement_attach', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_finance_statement_attach','total')){
                $table->dropColumn('total');
            }
			if(Schema::hasColumn('tbl_finance_statement_attach','gst')){
                $table->dropColumn('gst');
            }
			if(Schema::hasColumn('tbl_finance_statement_attach','sub_total')){
                $table->dropColumn('sub_total');
            }
        });
    }
}
