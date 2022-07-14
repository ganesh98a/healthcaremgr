<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblReferenceDataTypeNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $ids = array(12,13,16);
         foreach ($ids as $id){
        DB::table('tbl_reference_data_type')
            ->where('id',$id)
            ->update([
                "archive" => 1
        ]);
       }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
