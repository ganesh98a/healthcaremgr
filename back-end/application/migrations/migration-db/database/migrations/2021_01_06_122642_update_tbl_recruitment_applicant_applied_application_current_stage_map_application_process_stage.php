<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblRecruitmentApplicantAppliedApplicationCurrentStageMapApplicationProcessStage extends Migration
{
    public function up()
    {
        $numbers =array(
            0 => [0],  // New
            1 => [1],  // Screening
            2 => [2,3,4,5,15,16,17,19,20,23,21,22,24], // Interviews
            3 => [8] , // references
            4 => [6,7] ,  //Documents
            5 => [9,10,11,12], //CAB
            6 => [18] // Offer
          );
            
        
            $items = DB::select("select * from tbl_recruitment_applicant_applied_application");
            
            foreach($items as $key => $value) { 
              $id = $value->id; 
              $update_value = 0;
              
              foreach($numbers as $num_key => $num_value) {
                if(in_array($value->current_stage, $num_value) || $value->current_stage == 14) {          
        
                  if($value->status==3) {
                    $update_value = 7; // Hired
                  }else if($value->status==2){
                    $update_value = 8; // Unsuccessfull
                  } else {
                    $update_value = $num_key;
        
                  }  
                  
                  DB::table('tbl_recruitment_applicant_applied_application')
                ->where('id',$id)          
                ->update(["application_process_status" => $update_value]);
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
        //
    }
}
