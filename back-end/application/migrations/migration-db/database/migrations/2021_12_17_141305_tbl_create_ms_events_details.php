<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblCreateMsEventsDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_ms_events_details')) {
            Schema::create('tbl_ms_events_details', function (Blueprint $table) {
                $table->increments('id');
                $table->text('subject')->nullable();
                $table->text('event_id')->nullable();
                $table->string('organizer_name')->nullable();
                $table->text('organizer_email')->nullable();
                $table->longText('join_url')->nullable();
                $table->text('start_date_time_zone')->nullable();
                $table->text('end_date_time_zone')->nullable();
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
        if (Schema::hasTable('tbl_ms_events_details')) {
            Schema::dropIfExists('tbl_ms_events_details');
        }
    }
}
