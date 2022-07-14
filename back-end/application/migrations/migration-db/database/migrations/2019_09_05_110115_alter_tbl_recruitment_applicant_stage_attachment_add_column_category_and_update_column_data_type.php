<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantStageAttachmentAddColumnCategoryAndUpdateColumnDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_stage_attachment')) {
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','created')) {
                    $table->dateTime('created')->default('0000-00-00 00:00:00')->comment('when attachment attached date for this applicant');
                }
                if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','attachment')) {
                    $table->string('attachment',200)->nullable()->comment('attachment file name')->change();
                }
                if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','attachment_title')) {
                    $table->string('attachment_title',60)->nullable()->comment('attachment title')->change();
                }
                
                if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','stage')) {
                    $table->unsignedInteger('stage')->default(0)->comment('primary key of tbl_recruitment_stage table')->change();
                }
                
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','doc_category')) {
                    $table->unsignedInteger('doc_category')->default(0)->comment('primary key of tbl_recruitment_job_requirement_docs table');
                }

                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','archive')) {
                    $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data');
                }

                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','archive_at')) {
                    $table->dateTime('archive_at')->default('0000-00-00 00:00:00')->comment('when attachment archive date on this applicant');
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
        if (Schema::hasTable('tbl_recruitment_applicant_stage_attachment')) {
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','archive_at')){
                    $table->dropColumn('archive_at');
                }

                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','archive')){
                    $table->dropColumn('archive');
                } 

                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','doc_category')){
                    $table->dropColumn('doc_category');
                } 

                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','created')){
                    $table->dropColumn('created');
                }             
            });

        }
    }
}
