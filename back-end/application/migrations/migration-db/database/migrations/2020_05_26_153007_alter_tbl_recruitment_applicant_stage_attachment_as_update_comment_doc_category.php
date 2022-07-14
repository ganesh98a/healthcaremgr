<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantStageAttachmentAsUpdateCommentDocCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            $table->unsignedInteger('doc_category')->unsigned()->comment('tbl_references.id where type = 4 or 5')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            $table->unsignedInteger('doc_category')->unsigned()->comment('primary key of tbl_recruitment_job_requirement_docs table
')->change();
        });
    }
}
