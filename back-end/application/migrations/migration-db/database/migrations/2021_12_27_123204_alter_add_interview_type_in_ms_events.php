<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddInterviewTypeInMsEvents extends Migration
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
               if (!Schema::hasColumn('tbl_ms_events_logs','interview_type_id')) {
                  $table->unsignedInteger('interview_type_id')->after('interview_id')->comment('id of tbl_recruitment_interview_type')->nullable();
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
        if(Schema::hasTable('tbl_ms_events_logs') && Schema::hasColumn('tbl_ms_events_logs', 'tbl_ms_events_logs') ) {
            Schema::table('tbl_ms_events_logs', function (Blueprint $table) {
                $table->dropColumn('interview_type_id');
            });
        }
    }
}
