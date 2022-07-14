<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentFormApplicantAddReferenceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (Schema::hasTable('tbl_recruitment_form_applicant')) {
			Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
				if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'reference_id')) {
                     $table->unsignedBigInteger('reference_id')->comment('tbl_recruitment_applicant_reference.id its onynly come when reference interview')->after("applicant_id");
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
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_form_applicant')) {
			Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
				if (Schema::hasColumn('tbl_recruitment_form_applicant', 'reference_id')) {
                     $table->dropColumn('reference_id');
                }
			});
		}
        });
    }
}
