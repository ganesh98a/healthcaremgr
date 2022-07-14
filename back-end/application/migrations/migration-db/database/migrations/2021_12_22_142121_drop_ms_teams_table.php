<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropMsTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ms_events_details')) {
            Schema::dropIfExists('tbl_ms_events_details');
        }

        if (!Schema::hasTable('tbl_ms_events_logs')) {
            Schema::create('tbl_ms_events_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('interview_id')->comment('tbl_recruitment_interview');
                $table->longText('event_id')->nullable();               
                $table->text('subject')->nullable();
                $table->longText('onlineMeeting')->nullable();
                $table->string('onlineMeetingProvider')->nullable();
                $table->text('onlineMeetingUrl')->nullable();
                $table->text('organizer')->nullable();
                $table->string('responseRequested',50)->nullable();
                $table->text('responseStatus')->nullable();
                $table->longText('attendees')->nullable();
                
                $table->string('createdDateTime',255)->nullable();
                $table->text('start')->nullable();               
                $table->text('end')->nullable();
                $table->string('originalEndTimeZone')->nullable();
                $table->string('originalStartTimeZone')->nullable();

                $table->text('odata_context')->nullable();
                $table->text('odata_etag')->nullable();
                $table->string('allowNewTimeProposals',50)->nullable();
                $table->string('hasAttachments',50)->nullable();
                $table->string('hideAttendees',50)->nullable();
                $table->text('iCalUId')->nullable();
                
                $table->string('importance')->nullable();
                $table->string('isAllDay',50)->nullable();
                $table->string('isCancelled',50)->nullable();
                $table->string('isDraft',50)->nullable();
                $table->string('isOnlineMeeting',50)->nullable();
                $table->string('isOrganizer',50)->nullable();
                $table->string('isReminderOn',50)->nullable();
                $table->string('lastModifiedDateTime',255)->nullable();
                $table->text('location')->nullable();
                $table->text('locations')->nullable();
                $table->string('occurrenceId')->nullable();
               
                
                $table->string('recurrence')->nullable();
                $table->unsignedInteger('reminderMinutesBeforeStart')->nullable();               
                $table->string('sensitivity',255)->nullable();
                $table->string('seriesMasterId')->nullable();
                $table->string('showAs',255)->nullable();
                
                $table->string('type',255)->nullable();
                $table->longText('webLink')->nullable();

                $table->smallInteger('archive')->default(0);
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by')->references('id')->on('tbl_users'); 
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
        if (Schema::hasTable('tbl_ms_events_logs')) {
            Schema::dropIfExists('tbl_ms_events_logs');
        }
    }
}
