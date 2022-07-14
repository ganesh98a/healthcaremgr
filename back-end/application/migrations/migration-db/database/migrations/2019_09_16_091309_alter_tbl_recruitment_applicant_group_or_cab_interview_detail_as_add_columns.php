<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupOrCabInterviewDetailAsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            $table->unsignedInteger('mark_as_no_show')->after('document_status')->comment('1 -mark as no show, 0 -no mark')->default('0');            
            $table->dateTime('marked_date')->after('mark_as_no_show')->default('0000-00-00 00:00:00')->comment('date of mark_as_no_show');           
            $table->unsignedInteger('applicant_status')->after('quiz_status')->comment('1 -Successful, 0 -Pending, 2 -Unsuccessful');            
            $table->unsignedInteger('contract_status')->after('applicant_status')->comment('1 -Successful, 0 -Pending');    
            $table->unsignedInteger('quiz_status')->comment('1 -Successful, 0 -Pending, 2 -Unsuccessful')->change();        
            $table->unsignedInteger('quiz_status_overseen_by')->comment('Admin-Member id who udated Quiz status');        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
             $table->dropColumn('quiz_status_overseen_by');
             $table->dropColumn('mark_as_no_show');
             $table->dropColumn('marked_date');
             $table->dropColumn('applicant_status');
             $table->dropColumn('contract_status');
              $table->unsignedInteger('quiz_status')->comment('status of Quiz,Column used for CAB/Group interview day')->change();
        });
    }
}
