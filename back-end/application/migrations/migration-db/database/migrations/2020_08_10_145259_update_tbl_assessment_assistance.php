<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblAssessmentAssistance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $ids = array(2,10,13);
        foreach ($ids as $id){
       DB::table('tbl_assessment_assistance')
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
