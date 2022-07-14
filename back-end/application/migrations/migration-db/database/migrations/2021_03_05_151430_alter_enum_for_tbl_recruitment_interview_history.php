<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEnumForTblRecruitmentInterviewHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */    
    public function up()
    {
            Schema::table('tbl_recruitment_interview_field_history', function (Blueprint $table) {            
                if (Schema::hasColumn('tbl_recruitment_interview_field_history', 'field')){           
                    DB::statement("ALTER TABLE `tbl_recruitment_interview_field_history` CHANGE `field` `field` ENUM('title','interview_start_datetime','interview_end_datetime','location_id','interview_type_id','owner','description','max_applicant','invite_type','form_id','meeting_link')"); 
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
        //
    }
}
