<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentFormApplicantQuestionAsAddCreatedBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form_applicant_question', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_recruitment_form_applicant_question', 'created_by')) {
                $table->unsignedInteger('created_by')->after('archive')->default(0)->comment('tbl_member.id');
            }

            if(!Schema::hasColumn('tbl_recruitment_form_applicant_question','created')){
                    $table->dateTime('created')->default('0000-00-00 00:00:00');
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
        Schema::table('tbl_recruitment_form_applicant_question', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_form_applicant_question', 'created_by')) {
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('tbl_recruitment_form_applicant_question', 'created')) {
                $table->dropColumn('created');
            }
        });
    }
}
