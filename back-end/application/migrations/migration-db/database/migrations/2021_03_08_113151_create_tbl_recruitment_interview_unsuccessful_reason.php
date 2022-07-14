<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentInterviewUnsuccessfulReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_interview_unsuccessful_reason', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('interview_id')->comment('tbl_recruitment_interview');
            $table->unsignedInteger('reason')->comment('tbl_references.id and with type tbl_reference_data_type.key_name = "unsuccessful_group_booking_reason"');
            $table->text('reason_note');
            $table->dateTime('created');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_interview_unsuccessful_reason');
    }
}
