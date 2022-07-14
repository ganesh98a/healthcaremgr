<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantPresentationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_applicant_presentation', function (Blueprint $table) {
            $table->increments('id');            
            $table->smallInteger('interview_type')->comment('primary key of "tbl_recruitment_interview_type"');
            $table->string('file_name',100)->nullable();
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->unsignedTinyInteger('archive')->default('0')->comment('0- not/ 1 - archive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_applicant_presentation');
    }
}
