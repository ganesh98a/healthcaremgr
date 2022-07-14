<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentActionApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_action_applicant')) {
            Schema::create('tbl_recruitment_action_applicant', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('action_id');
                $table->unsignedInteger('applicant_id');
                $table->string('applicant_message',200)->comment('reply by applicant end');
                $table->unsignedTinyInteger('email_status')->comment('0=No , 1= Yes');
                $table->unsignedTinyInteger('status')->comment('0- Pending, 1- Approved, 2=Declined');
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
        Schema::dropIfExists('tbl_recruitment_action_applicant');
    }
}
