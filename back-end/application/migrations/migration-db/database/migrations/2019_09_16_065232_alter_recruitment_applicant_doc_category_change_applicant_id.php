<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantDocCategoryChangeApplicantId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_doc_category', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_doc_category', 'applicantId')) {
                $table->renameColumn('applicantId', 'applicant_id');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_doc_category', 'requirement_docs_id')) {
                $table->renameColumn('requirement_docs_id', 'recruitment_doc_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_doc_category', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_doc_category', 'applicant_id')) {
                $table->renameColumn('applicant_id', 'applicantId');
            }
            
            if (Schema::hasColumn('tbl_recruitment_applicant_doc_category', 'recruitment_doc_id')) {
                $table->renameColumn('recruitment_doc_id', 'requirement_docs_id');
            }
        });
    }

}
