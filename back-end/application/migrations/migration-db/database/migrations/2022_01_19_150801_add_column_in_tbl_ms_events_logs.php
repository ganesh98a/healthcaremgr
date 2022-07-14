<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInTblMsEventsLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ms_events_logs')) {
            Schema::table('tbl_ms_events_logs', function (Blueprint $table) {
               if (!Schema::hasColumn('tbl_ms_events_logs','event_status')) {
                  $table->smallInteger('event_status')->after('event_id')->default(0)->comment('0- not cancel / 1- cancelled');
              }              
          });
        }
        if (Schema::hasTable('tbl_recruitment_interview_applicant')) {
            Schema::table('tbl_recruitment_interview_applicant', function (Blueprint $table) {
               if (!Schema::hasColumn('tbl_recruitment_interview_applicant','event_status')) {
                  $table->smallInteger('event_status')->after('attendee_response')->default(0)->comment('0- not cancel / 1- cancelled');
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
        if(Schema::hasTable('tbl_ms_events_logs') && Schema::hasColumn('tbl_ms_events_logs', 'event_status') ) {
            Schema::table('tbl_ms_events_logs', function (Blueprint $table) {
                $table->dropColumn('event_status');
            });
        }
        if(Schema::hasTable('tbl_recruitment_interview_applicant') && Schema::hasColumn('tbl_recruitment_interview_applicant', 'event_status') ) {
            Schema::table('tbl_recruitment_interview_applicant', function (Blueprint $table) {
                $table->dropColumn('event_status');
            });
        }
    }
}
