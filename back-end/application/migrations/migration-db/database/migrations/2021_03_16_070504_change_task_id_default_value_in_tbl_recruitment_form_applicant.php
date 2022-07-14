<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTaskIdDefaultValueInTblRecruitmentFormApplicant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_form_applicant')) {
            Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
                $table->unsignedInteger('task_id')->default(0)->change();
            });
        }

        // updating existing task_id default to 0
        $form_applicant  = DB::select("SELECT rfa.id, rfa.task_id FROM `tbl_recruitment_form_applicant` as rfa WHERE rfa.task_id IS NULL");                  
        foreach($form_applicant as $val) {
           if(empty($val->task_id)){
               DB::table('tbl_recruitment_form_applicant')
               ->where("id",$val->id)
               ->update(["task_id" => 0]);
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
        if (Schema::hasTable('tbl_recruitment_form_applicant')) {
            Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
                $table->unsignedInteger('task_id')->default(0)->change();
            });
        }
    }
}
