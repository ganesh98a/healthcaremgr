<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddColumnFundingTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
        if (Schema::hasTable('tbl_shift')) {
            Schema::table('tbl_shift', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_shift','funding_type')){
                    $table->unsignedInteger('funding_type')->nullable()->default(0)->comment('tbl_funding_type auto increment id');
                }

            });

            if(Schema::hasColumn('tbl_shift','funding_type')){
                $pr = DB::table('tbl_funding_type')->where('name', 'NDIS')->where('archive', 0)->first();
                if(isset($pr->id)){
                    DB::table('tbl_shift')->where('funding_type', 0)->update(array('funding_type' => $pr->id));  
                }
            } 

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_shift')) {
            Schema::table('tbl_shift', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_shift','funding_type')){
                    $table->dropColumn('funding_type');
                } 
            });

        }
    }
}
