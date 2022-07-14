<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsAddColumnIsRequired extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_additional_questions')) {
            Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_additional_questions', 'is_required')) {
                    $table->unsignedSmallInteger('is_required')->default(0)->comment("0 - Not required/1 - required")->after('is_answer_optional');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_recruitment_additional_questions')) {
            Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_additional_questions', 'is_required')) {
                    $table->dropColumn('is_required');
                }
            });
        }   
    }
}
