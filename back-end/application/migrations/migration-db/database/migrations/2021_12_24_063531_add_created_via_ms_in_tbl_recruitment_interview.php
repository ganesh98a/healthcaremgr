<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedViaMsInTblRecruitmentInterview extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_interview')) {
            Schema::table('tbl_recruitment_interview', function (Blueprint $table) {
               if (!Schema::hasColumn('tbl_recruitment_interview','meeting_created_via')) {
                  $table->unsignedInteger('meeting_created_via')->after('interview_stage_status')->comment('1 = MS, 2 = Others')->nullable();
              }
              if (!Schema::hasColumn('tbl_recruitment_interview','ms_event_log_id')) {
                $table->unsignedInteger('ms_event_log_id')->after('meeting_created_via')->comment('id of tbl_ms_events_logs')->nullable();
            }
          });
        }

        if (Schema::hasTable('tbl_recruitment_interview_applicant')) {
            Schema::table('tbl_recruitment_interview_applicant', function (Blueprint $table) {
               if (!Schema::hasColumn('tbl_recruitment_interview_applicant','attendee_response')) {
                  $table->unsignedInteger('attendee_response')->comment('1 = Accepted, 2 = Tentative, 3-Declined')->nullable();
               }
              
          });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('tbl_recruitment_interview') && Schema::hasColumn('tbl_recruitment_interview', 'meeting_created_via') ) {
            Schema::table('tbl_recruitment_interview', function (Blueprint $table) {
                $table->dropColumn('meeting_created_via');
                $table->dropColumn('ms_event_log_id');
            });
        }

        if(Schema::hasTable('tbl_recruitment_interview_applicant') && Schema::hasColumn('tbl_recruitment_interview_applicant', 'attendee_response') ) {
            Schema::table('tbl_recruitment_interview_applicant', function (Blueprint $table) {
                $table->dropColumn('attendee_response');
            });
        }
    }
}
