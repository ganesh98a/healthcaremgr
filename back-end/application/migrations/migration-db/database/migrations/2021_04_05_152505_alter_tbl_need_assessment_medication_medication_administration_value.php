<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentMedicationMedicationAdministrationValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $medication_list  = DB::select("SELECT id, medication_administration FROM `tbl_need_assessment_medication` where medication_administration=2 and archive=0");                  
         
       

        foreach($medication_list as $val) {
            // update medication_administration
            if($val->medication_administration==2){
                DB::table('tbl_need_assessment_medication')
                ->where("id",$val->id)
                ->update(["medication_administration" => '1']);
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
        //
    }
}
