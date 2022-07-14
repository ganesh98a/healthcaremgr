<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftUpdateDefaultValueColumnFundingTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        if (Schema::hasTable('tbl_shift')) {
            if(Schema::hasColumn('tbl_shift','funding_type')){
                $pr = DB::table('tbl_funding_type')->where('name', 'NDIS')->where('archive', 0)->first();
                $defualtId = 0;
                if(isset($pr->id)){
                    $defualtId =   $pr->id;
                }
            } 
            Schema::table('tbl_shift', function (Blueprint $table) use($defualtId) {
                if(Schema::hasColumn('tbl_shift','funding_type')){
                    $table->unsignedInteger('funding_type')->nullable()->default($defualtId)->comment('tbl_funding_type auto increment id')->change();
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
    
    }
}
