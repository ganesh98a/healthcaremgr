<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsAsUpdateComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_additional_questions')) {
            Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_additional_questions','status')) {
                    $table->unsignedSmallInteger('status')->unsigned()->comment('1-Active, 2-Inactive')->change();
                }
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
        if (Schema::hasTable('tbl_recruitment_additional_questions')) {
            Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_additional_questions','status')) {
                    $table->unsignedSmallInteger('status')->unsigned()->comment('1- Active, 0- Inactive')->change();
                }
            });
        }
    }
}
