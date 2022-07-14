<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatedTableTblRecruitmentApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id')->nullable()->comment('autoincriment id of tbl_recruitment_applicant table');
            $table->unsignedInteger('position_applied')->nullable()->comment('autoincriment id of tbl_recruitment_job_position table');
            $table->unsignedInteger('recruitment_area')->nullable()->comment('autoincriment id of tbl_recruitment_department table');
            $table->unsignedInteger('employement_type')->nullable()->comment('autoincriment id of tbl_recruitment_job_employment_type table');
            $table->unsignedInteger('channelId')->nullable()->comment('autoincriment id of tbl_recruitment_channel table');
            $table->unsignedTinyInteger('status')->default(1)->nullable()->comment('application status 1 for pending');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
            $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data(delete)'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_applicant_applied_application');
    }
}
