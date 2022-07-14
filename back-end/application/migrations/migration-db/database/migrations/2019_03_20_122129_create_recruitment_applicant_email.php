<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_applicant_email')) {
            Schema::create('tbl_recruitment_applicant_email', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id');
                $table->string('email',50);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('primary_email')->comment('1- Primary, 2- Secondary');
                $table->unsignedTinyInteger('archive')->comment('1-archieved');
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
        Schema::dropIfExists('tbl_recruitment_applicant_email');
    }
}
