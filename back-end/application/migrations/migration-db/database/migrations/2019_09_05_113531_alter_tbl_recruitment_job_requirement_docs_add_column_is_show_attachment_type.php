<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobRequirementDocsAddColumnIsShowAttachmentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_job_requirement_docs')) {
            Schema::table('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_job_requirement_docs','is_show_attachment_cat_type')) {
                    $table->unsignedSmallInteger('is_show_attachment_cat_type')->default(0)->comment('1- show on attachment document category drop down list otherwise not showing');
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
        if (Schema::hasTable('tbl_recruitment_job_requirement_docs')) {
            Schema::table('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_job_requirement_docs','is_show_attachment_cat_type')){
                    $table->dropColumn('is_show_attachment_cat_type');
                }     
            });
        }
    }
}
