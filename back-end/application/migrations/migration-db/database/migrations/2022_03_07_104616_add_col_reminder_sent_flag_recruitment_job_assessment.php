<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColReminderSentFlagRecruitmentJobAssessment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_job_assessment', 'is_reminder_sent')) {
                $table->unsignedInteger('is_reminder_sent')->default(0)->after('status')->comment('Reminder already sent 0-No/1-Yes'); 
                
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
        if(Schema::hasTable('tbl_recruitment_job_assessment') && Schema::hasColumn('tbl_recruitment_job_assessment', 'is_reminder_sent')) {
            Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
               
                $table->dropColumn('is_reminder_sent');
            });
            
          }
    }
}
