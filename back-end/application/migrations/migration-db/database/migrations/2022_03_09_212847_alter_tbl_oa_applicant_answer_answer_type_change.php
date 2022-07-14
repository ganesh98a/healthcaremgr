<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOaApplicantAnswerAnswerTypeChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            $table->text('answer_id')->nullable()->comment('provided answer id - tbl_recruitment_oa_answer_options.id, null is short answer')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            //$table->unsignedInteger('answer_id')->comment('provided answer id - tbl_recruitment_oa_answer_options.id, null is short answer')->nullable()->change();
        });
    }
}
