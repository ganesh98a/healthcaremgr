<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentApplicantGroupCapAnswerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('tbl_recruitment_applicant_group_cap_answer')) {
        Schema::create('tbl_recruitment_applicant_group_cap_answer', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id')->comment('auto increment id of tbl_recruitment_applicant table.');
            $table->unsignedInteger('question_id')->comment('auto increment id of tbl_recruitment_additional_questions table.');
            $table->unsignedInteger('type')->comment('0 -group_interview / 1 - cap_day');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
            $table->unsignedInteger('archive')->comment('1 - delete')->default('0');
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
        Schema::dropIfExists('tbl_recruitment_applicant_group_cap_answer');
    }
}
