<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoodleStatusInJobAssessmentComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_job_assessment')) {
                $table->unsignedInteger('status')->comment('1-Sent, 2-In progress, 3-Submitted, 4-Completed, 5-Link Expired, 6-Error, 7-moodle')->change();
                $table->unsignedSmallInteger('is_moodle')->after('status')->comment("0 - Not/1 - Yes")->default(0);


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
        Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_job_assessment')) {
                $table->unsignedInteger('status')->comment('')->change();
            }
            if (Schema::hasColumn('tbl_recruitment_job_assessment', 'is_moodle')) {
                $table->dropColumn('is_moodle');
            } 
        });        
    }
}
