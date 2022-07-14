<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsAnswerAsAddArchiveColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_additional_questions_answer', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_additional_questions_answer')) {
                $table->unsignedInteger('archive')->comment('1 - delete')->default('0');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('tbl_recruitment_additional_questions_answer', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_additional_questions_answer')) {
                $table->dropColumn('archive');
            }
        });

    }
}
