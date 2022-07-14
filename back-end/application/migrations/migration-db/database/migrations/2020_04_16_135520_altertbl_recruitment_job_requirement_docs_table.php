<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AltertblRecruitmentJobRequirementDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_job_requirement_docs', 'is_show_attachment_cat_type')) {
                $table->dropColumn('is_show_attachment_cat_type');
                $table->unsignedInteger('cat_type')->default(1)->comment('1- Job apply, 2 - Recrtuiment stages');
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
        Schema::table('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_job_requirement_docs', 'cat_type')) {
                $table->dropColumn('cat_type');
                $table->unsignedInteger('is_show_attachment_cat_type')->default(0)->comment('1- show on attachment document category drop down list otherwise not showing');
             }
         });
    }
}
