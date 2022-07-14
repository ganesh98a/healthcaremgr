<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentOaQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_oa_questions', function (Blueprint $table) {
            $table->text('suggest_answer')->nullable()->comment('if answer type is 4 only')->after('question');
        });
        Schema::table('tbl_recruitment_oa_questions', function (Blueprint $table) {
            $table->text('serial_no')->nullable()->comment('serial no for display')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_oa_questions', function (Blueprint $table) {
            $table->dropColumn('suggest_answer');
        });
        Schema::table('tbl_recruitment_oa_questions', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
}
