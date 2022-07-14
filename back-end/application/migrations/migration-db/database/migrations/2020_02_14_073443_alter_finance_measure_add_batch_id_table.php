<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceMeasureAddBatchIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if(Schema::hasTable('tbl_finance_measure')){
            Schema::table('tbl_finance_measure', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_measure','batchId')){
                    $table->string('batchId',20)->nullable()->after('kay_name');
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
         if(Schema::hasTable('tbl_finance_measure')){
            Schema::table('tbl_finance_measure', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_measure','batchId')){
                    $table->dropColumn('batchId');
                }                
            });
        }
    }
}
