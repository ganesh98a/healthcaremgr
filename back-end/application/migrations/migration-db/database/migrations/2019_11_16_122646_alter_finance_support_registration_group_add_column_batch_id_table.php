<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceSupportRegistrationGroupAddColumnBatchIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_support_registration_group', function (Blueprint $table) {
			if(!Schema::hasColumn('tbl_finance_support_registration_group','batchId')){
				$table->unsignedInteger('batchId')->default(0)->comment('unique id for identify registration group number (Used by CSV)');
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
        Schema::table('tbl_finance_support_registration_group', function (Blueprint $table) {
            
                if(Schema::hasColumn('tbl_finance_support_registration_group','batchId')){
                    $table->dropColumn('batchId');
                } 
            
        });
    }
}
