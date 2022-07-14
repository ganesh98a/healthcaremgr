<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMigrationForRelateTheTaskForCabGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $task_list  = DB::select("SELECT rt.id as task_id ,rfa.task_id as form_task_id, rt.task_status, rta.applicant_task_status, rt.status ,ragcid.quiz_submit_status , ragcid.quiz_status,  ragcid.recruitment_task_applicant_id, rta.applicant_id,rta.application_id, rt.task_name , rt.form_id, rt.task_Stage,rta.stage_label_id,rfa.id as form_applicant_id  FROM `tbl_recruitment_applicant_group_or_cab_interview_detail` ragcid join tbl_recruitment_task_applicant rta on ragcid.recruitment_task_applicant_id=rta.id join tbl_recruitment_task rt on rta.taskId=rt.id join tbl_recruitment_form_applicant rfa on rt.form_id=rfa.form_id and rta.applicant_id=rfa.applicant_id and rta.application_id=rfa.application_id where rta.archive=0 and (rfa.task_id=0 or rfa.task_id is null)");                  
         
        // tbl_recruitment_task status ---- > 0-Draft/1-Scheduled/2-Open/3-Inprogress/4-Submitted/5-Expired/6-Completed
        // tbl_recruitment_task_applicant applicant_task_status ---- > 1-Scheduled/2-Open/3-Inprogress/4-Submitted/5-Expired
        // Here unsuccessful marked as Expired

        foreach($task_list as $val) {
            // update task id in form
            if($val->task_id){
                DB::table('tbl_recruitment_form_applicant')
                ->where("id",$val->form_applicant_id)
                ->update(["task_id" => $val->task_id]);
            }
            //update expired hcm side quiz / portal as expired
            if($val->quiz_status==2){
                DB::table('tbl_recruitment_task')
                ->where("id",$val->task_id)
                ->update(["task_status" => 5]);

                DB::table('tbl_recruitment_task_applicant')
                ->where("id",$val->recruitment_task_applicant_id)
                ->update(["applicant_task_status" => 5]);

            }
            //update successful hcm side quiz / portal as submitted
            if($val->quiz_status==1 && $val->quiz_submit_status==1){
                DB::table('tbl_recruitment_task')
                ->where("id",$val->task_id)
                ->update(["task_status" => 6]);

                DB::table('tbl_recruitment_task_applicant')
                ->where("id",$val->recruitment_task_applicant_id)
                ->update(["applicant_task_status" => 4]);
            }
            //update successful hcm side quiz / portal as expired
            if($val->quiz_status==1 && $val->quiz_submit_status!=1){
                DB::table('tbl_recruitment_task')
                ->where("id",$val->task_id)
                ->update(["task_status" => 6]);

                DB::table('tbl_recruitment_task_applicant')
                ->where("id",$val->recruitment_task_applicant_id)
                ->update(["applicant_task_status" => 5]);
            }

            //update submitted in both side
            if($val->quiz_status==0 && $val->quiz_submit_status==1){
                DB::table('tbl_recruitment_task')
                ->where("id",$val->task_id)
                ->update(["task_status" => 4]);

                DB::table('tbl_recruitment_task_applicant')
                ->where("id",$val->recruitment_task_applicant_id)
                ->update(["applicant_task_status" => 4]);
            }
            //update inprogress in both side
            if($val->quiz_status==0 && $val->quiz_submit_status==3){
                DB::table('tbl_recruitment_task')
                ->where("id",$val->task_id)
                ->update(["task_status" => 3]);

                DB::table('tbl_recruitment_task_applicant')
                ->where("id",$val->recruitment_task_applicant_id)
                ->update(["applicant_task_status" => 3]);
            }

            //update as draft in hcm side
            if($val->quiz_status==0 && $val->quiz_submit_status==0){
                DB::table('tbl_recruitment_task')
                ->where("id",$val->task_id)
                ->update(["task_status" => 0]);
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
