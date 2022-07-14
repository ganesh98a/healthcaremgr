<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentOaApplicantAnswerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_oa_applicant_answer', function (Blueprint $table) {
            $table->smallInteger('answer_type')->default(NULL)->nullable()->comment('1 -Yes,0 - No')->after('answer_id');
            $table->smallInteger('is_correct')->default(NULL)->nullable()->comment('1 -Yes,0 - No')->after('answer_type');
            $table->unsignedInteger('grade')->default(NULL)->nullable()->comment('marks in numbers')->after('is_correct');
            $table->text('recruiter_comments')->nullable()->after('answer_text');
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
            $table->dropColumn('answer_type');
            $table->dropColumn('is_correct');
            $table->dropColumn('grade');
            $table->dropColumn('recruiter_comments');
        });
    }
}
