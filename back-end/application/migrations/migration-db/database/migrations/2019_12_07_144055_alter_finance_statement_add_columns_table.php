<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceStatementAddColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_statement', function (Blueprint $table) {
			
			
			if(!Schema::hasColumn('tbl_finance_statement','statement_file_path')){
				$table->string('statement_file_path',200);
			}
			if(!Schema::hasColumn('tbl_finance_statement','statement_type')){
				$table->smallInteger('statement_type')->comment('1 for auto generated statement,2- for manual generated statement');
			}
			if(!Schema::hasColumn('tbl_finance_statement','statement_for')){
				$table->unsignedInteger('statement_for')->comment('id of participant,Org,Site etc');
			}
			if(!Schema::hasColumn('tbl_finance_statement','booked_by')){
				$table->smallInteger('booked_by')->comment('1= Site / Home, 2= Participant, 3 =Location , 4 =org ,5 =sub org');
			}			
			if(!Schema::hasColumn('tbl_finance_statement','status')){
				$table->smallInteger('status')->comment('0 -Not Send/1 - Send');
			}			 
			if(!Schema::hasColumn('tbl_finance_statement','archive')){
				$table->smallInteger('archive')->comment('0 -Not/1 - Archive');
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
            //
        });
    }
}
