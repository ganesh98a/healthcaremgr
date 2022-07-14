<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobAsRenameColumnSaveAs extends Migration
{
    /**
     * Run the migrations. draft, scheduled, live and closed job
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_job')) {
            Schema::table('tbl_recruitment_job', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_job','save_as')) {
                    $table->renameColumn('save_as','job_status');
                }
            });
            Schema::table('tbl_recruitment_job', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_job','job_status')) {
                    $table->unsignedInteger('job_status')->comment('0 = draft,1 = posted on selected channel(scheduled), 2 = closed and 3 = live')->change();
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
       if (Schema::hasTable('tbl_recruitment_job')) {
            Schema::table('tbl_recruitment_job', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_job','job_status')) {
                    $table->renameColumn('job_status','save_as');
                }
            });
        }
    }
}
